# Get all knowledge gaps by information directly contained in the knowledge gaps
PREFIX ipbes: <http://ontology.ipbes.net/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?gap ?subchapter ?description WHERE {
    ?gap a ipbes:KnowledgeGap .
    OPTIONAL {
        ?gap ipbes:SubChapter ?subchapter 
        }
    OPTIONAL {
        ?gap ipbes:hasDescription ?description
        }
}
ORDER BY ?gap

# Get all knowledge gaps and get additional information through joins. here we connect Chapter with nested optional
PREFIX ipbes: <http://ontology.ipbes.net/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?gap ?subchapter ?chapter ?description WHERE {
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
}
ORDER BY ?gap