
# Knowledge gaps: Specific
PREFIX ipbes: <http://ontology.ipbes.net/report>
SELECT ?gap ?subchapter ?chapter ?description
WHERE {
  ?gap a ipbes:KnowledgeGap .
  OPTIONAL { ?gap ipbes:SubChapter ?subchapter }
  OPTIONAL { ?gap ipbes:hasDescription ?description }
}
ORDER BY ?gap

# Knowledge gaps: Query all graphs:
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
