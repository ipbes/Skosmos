<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Try multiple possible endpoints
$endpoints = [
    'http://localhost:3030/report/query',
    'http://localhost:3030/va/query',
    'http://localhost:3030/ds/query'
];

$query = <<<SPARQL
PREFIX ipbes: <http://ontology.ipbes.net/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?gap ?subchapter ?chapter ?description ?report WHERE {
    ?gap a ipbes:KnowledgeGap .
    OPTIONAL {
        ?gap ipbes:SubChapter ?subchapter .
        OPTIONAL {
            ?subchapter ipbes:Chapter ?chapter .
        }
    }
    OPTIONAL {
        ?gap ipbes:hasDescription ?description .
    }
    OPTIONAL {
        ?gap ipbes:hasReport ?report .
    }
}
ORDER BY ?gap
SPARQL;

// Function to try multiple endpoints
function try_query($endpoints, $query) {
    foreach ($endpoints as $endpoint) {
        try {
            $url = $endpoint . '?query=' . urlencode($query);
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'Accept: application/sparql-results+json',
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'endpoint' => $endpoint,
                        'data' => $data
                    ];
                }
            }
        } catch (Exception $e) {
            continue;
        }
    }
    return false;
}

$result = try_query($endpoints, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IPBES Knowledge Gaps with Descriptions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            max-width: 1200px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .debug {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .description {
            max-width: 500px;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <h1>IPBES Knowledge Gaps with Descriptions</h1>
    
    <?php if ($result): ?>
        <?php if (!empty($result['data']['results']['bindings'])): ?>
            <p class="success">Successfully connected to endpoint: <?= htmlspecialchars($result['endpoint']) ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Knowledge Gap</th>
                        <th>Description</th>
                        <th>Sub-chapter</th>
                        <th>Chapter</th>
                        <th>Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['data']['results']['bindings'] as $row): ?>
                        <tr>
                            <td>
                                <small><?= htmlspecialchars($row['gap']['value']) ?></small>
                            </td>
                            <td class="description">
                                <?= htmlspecialchars($row['description']['value'] ?? 'No description available') ?>
                            </td>
                            <td class="subchapter">
                                <?= htmlspecialchars($row['subchapter']['value'] ?? 'No subchapter available') ?>
                            </td>
                            <td class="chapter">
                                <?= htmlspecialchars($row['chapter']['value'] ?? 'No chapter available') ?>
                            </td>
                            <td class="report">
                                <?= htmlspecialchars($row['report']['value'] ?? 'No report available') ?>
                            </td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p>Found <?= count($result['data']['results']['bindings']) ?> knowledge gaps.</p>
        <?php else: ?>
            <p>No knowledge gaps found in the database.</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="error">Failed to connect to all endpoints</p>
    <?php endif; ?>
    
    <div class="debug">
        <h3>Debug Information</h3>
        <p><strong>Endpoints tried:</strong></p>
        <ul>
            <?php foreach ($endpoints as $endpoint): ?>
                <li><?= htmlspecialchars($endpoint) ?></li>
            <?php endforeach; ?>
        </ul>
        
        <p><strong>SPARQL Query:</strong></p>
        <pre><?= htmlspecialchars($query) ?></pre>
        
        <?php if ($result): ?>
            <p><strong>Raw Response Sample:</strong></p>
            <pre><?= htmlspecialchars(print_r(array_slice($result['data']['results']['bindings'], 0, 2), true)) ?></pre>
        <?php endif; ?>
    </div>
    
    <a href="index.php" class="back-link">← Back to Main Navigator</a>
</body>
</html>