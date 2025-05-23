<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SPARQL endpoint configuration
$fuseki_endpoint = 'http://localhost:3030/va/query';

// Get URL parameters
$report = $_GET['report'] ?? null;
$subchapter = $_GET['subchapter'] ?? null;

/**
 * Execute SPARQL query against endpoint
 */
function sparql_query($endpoint, $query) {
    $url = $endpoint . '?query=' . urlencode($query);
    $response = file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/sparql-results+json'
        ]
    ]));
    return json_decode($response, true);
}

/**
 * Print navigation link
 */
function printLink($label, $params) {
    $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
    echo "<li><a href='$url'>$label</a></li>";
}

/**
 * Display resource details. You can remove |skos:prefLabel|dcterms:title in optional to show numbers
 */
function showResourceDetails($endpoint, $resourceUri) {
    $query = "
        PREFIX ipbes: <http://ontology.ipbes.net/>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
        PREFIX dcterms: <http://purl.org/dc/terms/>
        
        SELECT ?p ?o ?label WHERE { 
            <$resourceUri> ?p ?o . 
            OPTIONAL { 
                ?o rdfs:label|skos:prefLabel|dcterms:title ?label 
            } 
        }
    ";
    
    $results = sparql_query($endpoint, $query);
    
    echo "<ul>";
    foreach ($results['results']['bindings'] as $row) {
        $prop = basename($row['p']['value']);
        $value = $row['o']['value'];
        $label = $row['label']['value'] ?? '';
        $displayValue = $label ? "$label ($value)" : $value;
        echo "<li><strong>$prop:</strong> $displayValue</li>";
    }
    echo "</ul>";
}

/**
 * Display reference persons
 */
function showReferencePersons($endpoint, $refUri) {
    $query = "
        PREFIX ipbes: <http://ontology.ipbes.net/>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        
        SELECT ?person ?label WHERE { 
            <$refUri> foaf:Person ?person . 
            OPTIONAL { 
                ?person foaf:name|rdfs:label ?label 
            } 
        }
    ";
    
    $results = sparql_query($endpoint, $query);
    
    if (!empty($results['results']['bindings'])) {
        echo "<ul>";
        foreach ($results['results']['bindings'] as $row) {
            $personLabel = $row['label']['value'] ?? basename($row['person']['value']);
            echo "<li>$personLabel</li>";
        }
        echo "</ul>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IPBES Report Navigator</title>
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
        h2, h3 {
            color: #34495e;
            margin-top: 25px;
        }
        ul {
            list-style-type: none;
            padding: 0;
            margin-left: 15px;
        }
        li {
            margin-bottom: 10px;
            padding: 5px;
        }
        a {
            text-decoration: none;
            color: #2980b9;
            padding: 3px 5px;
        }
        a:hover {
            text-decoration: underline;
            background-color: #f5f5f5;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        strong {
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <h1>IPBES Report Navigator</h1>
    
    <?php if (!$report && !$subchapter): ?>
        <!-- List all reports. You can remove |skos:prefLabel in optional-->
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            
            SELECT DISTINCT ?report ?label WHERE { 
                ?report a ipbes:Report . 
                OPTIONAL { 
                    ?report rdfs:label|skos:prefLabel ?label 
                } 
            } ORDER BY ?label
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h2>Available Reports</h2>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php $label = $row['label']['value'] ?? basename($row['report']['value']); ?>
                <?php printLink($label, ['report' => $row['report']['value']]); ?>
            <?php endforeach; ?>
        </ul>
        
    <?php elseif ($report && !$subchapter): ?>
        <!-- Show report details and list subchapters -->
        <h2>Report Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $report); ?>
        
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            
            SELECT ?subchapter ?label WHERE { 
                ?subchapter a ipbes:SubChapter ; 
                ipbes:Report <{$report}> . 
                OPTIONAL { 
                    ?subchapter rdfs:label ?label 
                } 
            } ORDER BY ?label
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h3>Subchapters</h3>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php $label = $row['label']['value'] ?? basename($row['subchapter']['value']); ?>
                <?php printLink($label, [
                    'report' => $report, 
                    'subchapter' => $row['subchapter']['value']
                ]); ?>
            <?php endforeach; ?>
        </ul>
        
        <a href="?" class="back-link">← Back to All Reports</a>
        
    <?php elseif ($subchapter): ?>
        <!-- Show subchapter details and references -->
        <h2>Subchapter Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $subchapter); ?>
        
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            
            SELECT ?ref ?doi ?label WHERE { 
                ?ref a ipbes:Reference ; 
                ipbes:hasReport <{$subchapter}> . 
                OPTIONAL { 
                    ?ref ipbes:hasDoi ?doi . 
                    ?ref rdfs:label ?label 
                } 
            } ORDER BY ?label
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h3>References</h3>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php
                $refLabel = $row['label']['value'] ?? basename($row['ref']['value']);
                $doiUrl = isset($row['doi']) ? "https://doi.org/" . ltrim($row['doi']['value'], 'doi:') : '';
                $doiLink = $doiUrl ? " (<a href='$doiUrl' target='_blank'>DOI</a>)" : '';
                ?>
                <li>
                    <?= $refLabel ?><?= $doiLink ?>
                    <?php showReferencePersons($fuseki_endpoint, $row['ref']['value']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <a href="?report=<?= urlencode($report) ?>" class="back-link">← Back to Subchapters</a>
    <?php endif; ?>
</body>
</html>