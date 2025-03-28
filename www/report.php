<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SPARQL endpoint for Fuseki
$fuseki_endpoint = 'http://localhost:3030/report/query';

// Get parameters
$report = $_GET['report'] ?? null;
$chapter = $_GET['chapter'] ?? null;

// Run SPARQL query
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

function printLink($label, $params) {
    $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
    echo "<li><a href='$url'>$label</a></li>";
}

function showResourceDetails($endpoint, $resourceUri) {
    $query = "PREFIX ipbes: <http://ontology.ipbes.net/>
              PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
              PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
              PREFIX dcterms: <http://purl.org/dc/terms/>
              SELECT ?p ?o ?label WHERE { 
                  <$resourceUri> ?p ?o . 
                  OPTIONAL { 
                      ?o rdfs:label|skos:prefLabel|dcterms:title ?label 
                  } 
              }";
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

function showReferencePersons($endpoint, $refUri) {
    $query = "PREFIX ipbes: <http://ontology.ipbes.net/>
              PREFIX foaf: <http://xmlns.com/foaf/0.1/>
              SELECT ?person ?label WHERE { 
                  <$refUri> foaf:Person ?person . 
                  OPTIONAL { 
                      ?person foaf:name|rdfs:label ?label 
                  } 
              }";
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
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IPBES Report Navigator</title>
    <style>
        body { font-family: Arial, sans-serif; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 8px; }
        a { text-decoration: none; color: #337ab7; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>IPBES Report Navigator</h1>
<?php
if (!$report && !$chapter) {
    $query = "PREFIX ipbes: <http://ontology.ipbes.net/>
              PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
              PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
              SELECT DISTINCT ?report ?label WHERE { 
                  ?report a ipbes:Report . 
                  OPTIONAL { 
                      ?report rdfs:label|skos:prefLabel ?label 
                  } 
              } ORDER BY ?label";
    $results = sparql_query($fuseki_endpoint, $query);
    echo "<h2>Available Reports</h2><ul>";
    foreach ($results['results']['bindings'] as $row) {
        $label = $row['label']['value'] ?? basename($row['report']['value']);
        printLink($label, ['report' => $row['report']['value']]);
    }
    echo "</ul>";
} elseif ($report && !$chapter) {
    echo "<h2>Report Details</h2>";
    showResourceDetails($fuseki_endpoint, $report);

    $query = "PREFIX ipbes: <http://ontology.ipbes.net/>
              PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
              PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
              SELECT ?chapter ?label WHERE { 
                  ?chapter a ipbes:Chapter ; 
                  ipbes:Report <{$report}> . 
                  OPTIONAL { 
                      ?chapter rdfs:label|skos:prefLabel ?label 
                  } 
              } ORDER BY ?label";
    $results = sparql_query($fuseki_endpoint, $query);
    echo "<h3>Chapters</h3><ul>";
    foreach ($results['results']['bindings'] as $row) {
        $label = $row['label']['value'] ?? basename($row['chapter']['value']);
        printLink($label, ['report' => $report, 'chapter' => $row['chapter']['value']]);
    }
    echo "</ul><p><a href='?'>Back to Reports</a></p>";
} elseif ($chapter) {
    echo "<h2>Chapter Details</h2>";
    showResourceDetails($fuseki_endpoint, $chapter);

    $query = "PREFIX ipbes: <http://ontology.ipbes.net/>
              PREFIX dcterms: <http://purl.org/dc/terms/>
              PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
              SELECT ?ref ?doi ?label WHERE { 
                  ?ref a ipbes:Reference ; 
                  ipbes:hasReport <{$chapter}> . 
                  OPTIONAL { 
                      ?ref ipbes:hasDoi|dcterms:identifier ?doi 
                  }
                  OPTIONAL { 
                      ?ref rdfs:label|skos:prefLabel|dcterms:title ?label 
                  } 
              } ORDER BY ?label";
    $results = sparql_query($fuseki_endpoint, $query);
    echo "<h3>References</h3><ul>";
    foreach ($results['results']['bindings'] as $row) {
        $refLabel = $row['label']['value'] ?? basename($row['ref']['value']);
        $doiUrl = isset($row['doi']) ? "https://doi.org/" . ltrim($row['doi']['value'], 'doi:') : '';
        $doiLink = $doiUrl ? " (<a href='$doiUrl'>DOI</a>)" : '';
        echo "<li>$refLabel$doiLink";
        showReferencePersons($fuseki_endpoint, $row['ref']['value']);
        echo "</li>";
    }
    echo "</ul><p><a href='?report=" . urlencode($report) . "'>Back to Chapters</a></p>";
}
?>
</body>
</html>