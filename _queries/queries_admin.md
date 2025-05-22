
# Administrator tasks
## Knowledge gaps: Query specific graphs (code, fuseki)
prefix ipbes: <http://ontology.ipbes.net/report>  
SELECT ?gap ?subchapter ?chapter  ?description 
WHERE { 
    GRAPH <http://ontology.ipbes.net/graph/ias> { 
    ?gap a ipbes:KnowledgeGap . 
    OPTIONAL { ?gap ipbes:SubChapter ?subchapter } 
    OPTIONAL { ?gap ipbes:hasDescription ?description } 
    }
} 
ORDER BY ?gap

## Query all graphs:
PREFIX ipbes: <http://ontology.ipbes.net/report>
SELECT ?g ?gap ?subchapter ?chapter ?description
WHERE {
  GRAPH ?g {
    ?gap a ipbes:KnowledgeGap .
    OPTIONAL { ?gap ipbes:SubChapter ?subchapter }
    OPTIONAL { ?gap ipbes:hasDescription ?description }
  }
}
ORDER BY ?gap

# Find name of all graphs: 
SELECT ?g 
WHERE { 
GRAPH ?g { } 
} 
  
# Delete one specific graph: 
  
DROP GRAPH <http://ontology.ipbes.net/graph/ias> 
  
# Find total number of triples in a graph: 
  
SELECT (COUNT(*) as ?count) 
where{ 
Graph <http://ontology.ipbes.net/graph/ias> { 

?x ?z ?y } 
} 
  
### Querying from a specific graph: 
  
WHERE{ 
GRAPH <http://ontology.ipbes.net/graph/ias> { 

?x ?y ?z 
} 

---

SELECT ?subject ?predicate ?object
WHERE {
  GRAPH <http://ontology.ipbes.net/graph/ias> { 
  ?subject ?predicate ?object
  }
}
LIMIT 25
