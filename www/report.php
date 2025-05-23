<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SPARQL endpoint configuration
$fuseki_endpoint = 'http://localhost:3030/report/query';

// Get URL parameters
$report = isset($_GET['report']) ? urldecode($_GET['report']) : null;
$chapter = isset($_GET['chapter']) ? urldecode($_GET['chapter']) : null;
$subchapter = isset($_GET['subchapter']) ? urldecode($_GET['subchapter']) : null;
$person = isset($_GET['person']) ? rawurldecode($_GET['person']) : null;


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
        PREFIX ipbes: <http://ontology.ipbes.net/report>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
        PREFIX dcterms: <http://purl.org/dc/terms/>
        
        SELECT ?g ?p ?o ?label 
        WHERE { 
          GRAPH ?g {  
            <$resourceUri> ?p ?o . 
            OPTIONAL { ?o rdfs:label|skos:prefLabel|dcterms:title ?label }
          }
        }
    ";
    
    $results = sparql_query($endpoint, $query);
    
    echo "<ul>";
    foreach ($results['results']['bindings'] as $row) {
        $prop = basename($row['p']['value']);
        $value = $row['o']['value'];
        $label = $row['label']['value'] ?? '';

        // Skip internal reference URIs (reportReference-style)
        if (strpos($value, 'http://ontology.ipbes.net/report/ref/') === 0) {
            continue;
        }

        // Handle person URIs
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if (strpos($value, '/person/') !== false) {
                $displayText = $label ?: basename($value);
                $encodedUri = rawurlencode($value);
                // Make URI clickable
                $displayValue = "$displayText (<a href='?person=$encodedUri'>$value</a>)";
            } else {
                $displayValue = $label ? "$label ($value)" : $value;
            }
        } else {
            $displayValue = $label ? "$label ($value)" : $value;
        }
        echo "<li><strong>$prop:</strong> $displayValue</li>";
    }
    echo "</ul>";
}

/**
 * Display reference persons
 */
function showReferencePersons($endpoint, $refUri) {
    $query = "
        PREFIX ipbes: <http://ontology.ipbes.net/report>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        
        SELECT DISTINCT ?g ?person ?label 
        WHERE { 
            GRAPH ?g {
                <{$refUri}> ipbes:hasPerson ?person . 
                OPTIONAL { ?person foaf:name|rdfs:label ?label }
            }
        }
    ";
    
    $results = sparql_query($endpoint, $query);

    if (!empty($results['results']['bindings'])) {
        echo "<ul>";
        foreach ($results['results']['bindings'] as $row) {
            $personLabel = $row['label']['value'] ?? basename($row['person']['value']);
            $personUri = $row['person']['value'];
            $encodedUri = rawurlencode($personUri);
            // Make the URI clickable
            echo "<li>$personLabel (<a href='?person=$encodedUri'>$personUri</a>)</li>";
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
    
    <?php if ($person): ?>
        <h2>Person Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $person); ?>
            <a href="javascript:history.back()" class="back-link">← Back</a>
    <?php elseif (!$report && !$chapter && !$subchapter ): ?>
        
        <!-- List all reports. You can remove |skos:prefLabel in optional-->
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/report>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            
            SELECT DISTINCT ?g ?report ?label 
            WHERE {
                GRAPH ?g { 
                    ?report a ipbes:Report . 
                    OPTIONAL { ?report rdfs:label|skos:prefLabel ?label }
                    } 
            }
            ORDER BY ?label
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
        
    <?php elseif ($report && !$chapter && !$subchapter): ?>
        <!-- Show report details and list chapters -->
        <h2>Report Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $report); ?>
        
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/report>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            
            SELECT ?g ?chapter ?label 
            WHERE { 
                GRAPH ?g {
                    ?chapter a ipbes:Chapter ; 
                    ipbes:Report <{$report}> . 
                    OPTIONAL { ?chapter rdfs:label|skos:prefLabel ?label } 
                }
            } 
            ORDER BY ?chapter
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h3>Chapters</h3>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php $label = $row['label']['value'] ?? basename($row['chapter']['value']); ?>
                <?php printLink($label, [
                    'report' => $report, 
                    'chapter' => $row['chapter']['value']
                ]); ?>
            <?php endforeach; ?>
        </ul>
        
        <a href="?" class="back-link">← Back to All Reports</a>
        
    <?php elseif ($report && $chapter && !$subchapter): ?>
        <!-- Show chapter details and list subchapters -->
        <h2>Chapter Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $chapter); ?>
        
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/report>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            
            SELECT ?g ?subchapter ?label 
            WHERE { 
                GRAPH ?g {
                    ?subchapter a ipbes:SubChapter ; 
                    ipbes:Chapter <{$chapter}> . 
                    OPTIONAL { ?subchapter rdfs:label|skos:prefLabel ?label } 
                }
            } ORDER BY ?subchapter
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h3>Subchapters</h3>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php $label = $row['label']['value'] ?? basename($row['subchapter']['value']); ?>
                <?php printLink($label, [
                    'report' => $report, 
                    'chapter' => $chapter,
                    'subchapter' => $row['subchapter']['value']
                ]); ?>
            <?php endforeach; ?>
        </ul>
        
        <a href="?report=<?= urlencode($report) ?>" class="back-link">← Back to Chapters</a>
        
    <?php elseif ($subchapter): ?>
        <!-- Show subchapter details and references -->
        <h2>Subchapter Details</h2>
        <?php showResourceDetails($fuseki_endpoint, $subchapter); ?>
        
        <?php
        $query = "
            PREFIX ipbes: <http://ontology.ipbes.net/report>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            PREFIX owl: <http://www.w3.org/2002/07/owl#>
            
            SELECT ?g ?ref ?doi ?label ?sameAs WHERE { 
            GRAPH ?g {
                    ?ref a ipbes:Reference ; 
                    ipbes:SubChapter <{$subchapter}> . 
                    OPTIONAL { ?ref ipbes:hasDoi ?doi . }
                    OPTIONAL { ?ref rdfs:label ?label . }
                    OPTIONAL { ?ref owl:sameAs ?sameAs . }
                    
                } 
            } ORDER BY ?label 
        ";
        $results = sparql_query($fuseki_endpoint, $query);
        ?>
        
        <h3>References</h3>
        <ul>
            <?php foreach ($results['results']['bindings'] as $row): ?>
                <?php
                $sameAs = $row['sameAs']['value'] ?? null;

                if ($sameAs) {
                    $refLabel = "<a href=\"$sameAs\" target=\"_blank\">$sameAs</a>";
                } elseif (!empty($row['label']['value'])) {
                    $refLabel = $row['label']['value'];
                } else {
                    $refLabel = "<a href=\"{$row['ref']['value']}\" target=\"_blank\">{$row['ref']['value']}</a>";
                }

                $doiUrl = isset($row['doi']) ? "https://doi.org/" . ltrim($row['doi']['value'], 'doi:') : '';
                $doiLink = $doiUrl ? " (<a href='$doiUrl' target='_blank'>DOI</a>)" : '';
                ?>
                <li>
                    <?= $refLabel ?><?= $doiLink ?>
                    <?php showReferencePersons($fuseki_endpoint, $row['ref']['value']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <a href="?report=<?= urlencode($report) ?>&chapter=<?= urlencode($chapter) ?>" class="back-link">← Back to Subchapters</a>
    <?php endif; ?>
</body>
</html>