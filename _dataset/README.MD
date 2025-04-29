# Project
https://github.com/ipbes/Skosmos/tree/main
original ttl files for ontology downloaded from https://github.com/IPBES-Data/IPBES_Ontology
original ttl files for LOD downloaded from https://github.com/IPBES-Data/IPBES_LOD

# Rewrite datasets 
## Simple find and replace
### Change foaf to skos
foaf:prefLabel
skos:prefLabel

## Regex replacements
### use prefix for reports
"http://ontology.ipbes.net/report/global19"
ipbes-report:$1
'''
"http://ontology\.ipbes\.net/report/([^>]+)"
ipbes-report:$1
'''
<http://ontology\.ipbes\.net/report/([^>]+)>
ipbes-report:$1
'''
ipbes:Report <http://ontology.ipbes.net/report/IAS23> ;
ipbes:hasReport ipbes-report:IAS23 ;
'''
ipbes:Report\s*<http://ontology\.ipbes\.net/report/([^>]+)>
ipbes:hasReport ipbes-report:$1
'''

### use prefix for background messages
<http://ontology.ipbes.net/report/bgm/IAS23-A2> 
ipbes-bgm:IAS23-A2
'''
<http://ontology\.ipbes\.net/report/bgm/([^>]+)>
ipbes-bgm:$1
'''
Sub message
<http://ontology.ipbes.net/report/subm/global19-A1-SM0>
ipbes-subm:IAS23-A2
'''
<http://ontology\.ipbes\.net/report/subm/([^>]+)>
ipbes-subm:$1
'''

    <http://ontology\.ipbes\.net/report/bgm/ → Matches the fixed URL part.
    ([^>]+) → Captures the unique report ID (IAS23-A2, etc.).
    > → Matches the closing >, ensuring only the right pattern is changed.
    $1 → Inserts the captured report ID into the replacement.

### use prefix for key message
<http://ontology.ipbes.net/report/key/IAS23-KM-B> 
ipbes-key:IAS23-KM-B
'''
<http://ontology\.ipbes\.net/report/key/([^>]+)>
ipbes-key:$1
'''

    <http://ontology\.ipbes\.net/report/key/ → Matches the fixed part of the URL.
    ([^>]+) → Captures the unique report ID (e.g., IAS23-KM-B).
    > → Matches the closing > of the URL.
    $1 → Inserts the captured report ID into the replacement.

### use prefix for SubChapter
<http://ontology.ipbes.net/report/sch/IAS23-2.5>
ipbes-sch:IAS23-2.5
<http://ontology\.ipbes\.net/report/sch/([^>]+)>
ipbes-sch:$1
'''

    <http://ontology\.ipbes\.net/report/sch/ → Matches the fixed part of the URL.
    ([^>]+) → Captures the unique identifier part (IAS23-2.5).
    > → Matches the closing >.
    $1 → Inserts the captured identifier into the replacement.

### use prefix for Chapter
<http://ontology.ipbes.net/report/ch/global19ch5>
ipbes-ch:global19ch5
<http://ontology\.ipbes\.net/report/ch/([^>]+)>
ipbes-ch:$1

### use prefix for knowledge gaps
<http://ontology.ipbes.net/report/kg/global19-167>
ipbes-sch:global19-167
<http://ontology\.ipbes\.net/report/kg/([^>]+)>
ipbes-kg:$1

### use prefix for illustrations
<http://ontology.ipbes.net/report/il/global19-FigureSPM.2>
ipbes-il:global19-FigureSPM.2
'''
<http://ontology\.ipbes\.net/report/il/([^>]+)>
ipbes-il:$1
'''

### use prefix for person
<http://ontology.ipbes.net/report/person/Lomba_Ângela>
ipbes-person:Lomba_Ângela
'''
<http://ontology\.ipbes\.net/report/person/([^>]+)>
ipbes-person:$1
'''
    <http://ontology\.ipbes\.net/report/person/ → Matches the fixed part of the URL.
    ([^>]+) → Captures the unique identifier part (Lomba_Ângela).
    > → Matches the closing >.
    $1 → Inserts the captured identifier into the replacement.

### add languages for identifiers
ipbes:identifier "B"
ipbes:identifier "B"@en
ipbes:identifier "GA_2.2.5.2.1_233"
ipbes:identifier "GA_2.2.5.2.1_233"@en
'''
(ipbes:identifier\s*")([^"]+)(")
$1$2"@en
'''
    (ipbes:identifier\s*") → Matches ipbes:identifier " and any spaces before the value.
    ([^"]+) → Captures the value inside the quotes (GA_2.1.17.2_127, etc.).
    (") → Matches the final closing quote.
    $1$2"@en → Keeps the captured groups ($1 for ipbes:identifier " and $2 for the identifier value), and appends @en before the final quote.
### add languages for labels and people
skos:prefLabel "Ângela Lomba"
skos:prefLabel "Ângela Lomba"@en
'''
(skos:prefLabel\s*")([^"]+)(")
$1$2"@en
'''
foaf:firstName "Ângela"
foaf:firstName "Ângela"@en
'''
(foaf:firstName\s*")([^"]+)(")
$1$2"@en
'''
foaf:lastName "Lomba"
foaf:lastName "Lomba"@en
'''
(foaf:lastName\s*")([^"]+)(")
$1$2"@en
'''
<http://ontology.ipbes.net/report/ref/2352922/items/UICP6JPF> should be
<http://data.ipbes.net/refm/2352922/items/UICP6JPF>

## remove spaces and fullstop
<http://ontology.ipbes.net/report/key/global19- A.> 
<http://ontology.ipbes.net/report/key/global19- B.>
<http://ontology.ipbes.net/report/key/global19- C.>

## use double quotes when names have apostrophes
ipbes-person:o’callaghan_cristina a foaf:Person ;
ipbes-person:"o'callaghan_cristina" a foaf:Person ;
'''
    Alternatively, you can use double quotes (") to avoid confusion with the single quote inside the identifier:


## subchapter should not include bracket e.g. 
ipbes-sch:global19-3.3.2.2(SustainableDevelopmentGoal3) ;
ipbes-sch:global19-2.3.5.2(NCP2and12) ;
'''

    Expected punctuation to follow "http://data.ipbes.net/sch/global19-3.3.2.2" at line 44985.
    Expected punctuation to follow "http://data.ipbes.net/sch/global19-2.3.5.2" at line 45000.

ipbes-sch:global19-2.1.2. ;
ipbes:SubChapter ipbes-sch:global19-4.2.4.3.,
    Expected subject but got ; at line 45502.

# Next steps
Direction/Objectives
* Rest API
* LOD + Queries 
* *  Examples
* Glossary | Source to remain in PoolParty

Review/Test TTL files
* Consistency
* Syntax testing

Further development and theming
Architecture to publish web page
Check if multiple identifiers are fine

# Run tests 
https://www.w3.org/2015/03/ShExValidata/
https://felixlohmeier.github.io/turtle-web-editor/

# Credits
Juan Antonio Pastor Sánchez
Profesor Titular
Universidad de Murcia
Departamento de Información y Documentación
pastor@um.es
Tel.: +34 868 88 7252
https://www.um.es/docencia/pastor
