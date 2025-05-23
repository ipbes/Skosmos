<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$fuseki_endpoint = 'http://localhost:3030/report/query';

$report = $_GET['report'] ?? null;
$chapter = $_GET['chapter'] ?? null;
$subchapter = $_GET['subchapter'] ?? null;
$person = $_GET['person'] ?? null;

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
    $query = "
        PREFIX ipbes: <http://ontology.ipbes.net/report>
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
        PREFIX dcterms: <http://purl.org/dc/terms/>
        PREFIX foaf: <http://xmlns.com/foaf/0.1/>

        SELECT ?g ?p ?o ?label ?otype
        WHERE {
            GRAPH ?g {
                <$resourceUri> ?p ?o .
                OPTIONAL { ?o rdfs:label|skos:prefLabel|dcterms:title ?label }
                OPTIONAL { ?o a ?otype }
            }
        }
    ";

    $results = sparql_query($endpoint, $query);

    echo "<ul>";
    foreach ($results['results']['bindings'] as $row) {
        $prop = basename($row['p']['value']);
        $value = $row['o']['value'];
        $label = $row['label']['value'] ?? '';
        $otype = $row['otype']['value'] ?? '';

        $internalLink = null;
        if ($otype === 'http://xmlns.com/foaf/0.1/Person') {
            $internalLink = "?person=" . urlencode($value);
        } elseif ($otype === 'http://ontology.ipbes.net/report#Report') {
            $internalLink = "?report=" . urlencode($value);
        } elseif ($otype === 'http://ontology.ipbes.net/report#Chapter') {
            $internalLink = "?chapter=" . urlencode($value);
        } elseif ($otype === 'http://ontology.ipbes.net/report#SubChapter') {
            $internalLink = "?subchapter=" . urlencode($value);
        }

        if ($internalLink) {
            $displayValue = $label ? "$label (<a href='$internalLink'>$value</a>)" : "<a href='$internalLink'>$value</a>";
        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
            $displayValue = $label ? "$label (<a href='$value' target='_blank'>$value</a>)" : "<a href='$value' target='_blank'>$value</a>";
        } else {
            $displayValue = htmlspecialchars($value);
        }

        echo "<li><strong>$prop:</strong> $displayValue</li>";
    }
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IPBES Report Navigator</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2, h3 { color: #34495e; margin-top: 25px; }
        ul { list-style-type: none; padding: 0; margin-left: 15px; }
        li { margin-bottom: 10px; padding: 5px; }
        a { text-decoration: none; color: #2980b9; padding: 3px 5px; }
        a:hover { text-decoration: underline; background-color: #f5f5f5; }
        .back-link { display: inline-block; margin-top: 20px; padding: 8px 15px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; }
        strong { color: #7f8c8d; }
    </style>
</head>
<body>
<h1>IPBES Report Navigator</h1>

<?php
if ($person): ?>
    <h2>Person Details</h2>
    <?php showResourceDetails($fuseki_endpoint, $person); ?>
    <a href="javascript:history.back()" class="back-link">← Back</a>
<?php elseif ($subchapter): ?>
    <h2>Subchapter Details</h2>
    <?php showResourceDetails($fuseki_endpoint, $subchapter); ?>
    <a href="?report=<?= urlencode($report) ?>&chapter=<?= urlencode($chapter) ?>" class="back-link">← Back to Subchapters</a>
<?php elseif ($chapter): ?>
    <h2>Chapter Details</h2>
    <?php showResourceDetails($fuseki_endpoint, $chapter); ?>
    <a href="?report=<?= urlencode($report) ?>" class="back-link">← Back to Chapters</a>
<?php elseif ($report): ?>
    <h2>Report Details</h2>
    <?php showResourceDetails($fuseki_endpoint, $report); ?>
    <a href="?" class="back-link">← Back to All Reports</a>
<?php else: ?>
    <h2>Available Reports</h2>
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
    <ul>
        <?php foreach ($results['results']['bindings'] as $row): ?>
            <?php $label = $row['label']['value'] ?? basename($row['report']['value']); ?>
            <?php printLink($label, ['report' => $row['report']['value']]); ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
