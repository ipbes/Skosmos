<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SPARQL Query Search</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
      color: #333;
    }

    header {
      background-color: #009390;
      color: white;
      padding: 1em 2em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    header .logo {
      font-size: 1.5em;
      font-weight: bold;
    }

    main {
      padding: 2em;
      max-width: 960px;
      margin: auto;
      background-color: white;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    h1 {
      color: #515648;
    }

    label, select, input, textarea {
      display: block;
      width: 100%;
      margin: 0.5em 0;
    }

    select, input, textarea {
      padding: 0.5em;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1em;
    }

    textarea {
      height: 200px;
    }

    button {
      padding: 0.75em 1.5em;
      background-color: #005fa3;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 1em;
    }

    button:hover {
      background-color: #009390;
    }

    #results {
      background-color: #f1f1f1;
      padding: 1em;
      margin-top: 1.5em;
      white-space: pre-wrap;
      border-radius: 4px;
      border: 1px solid #ddd;
    }

    #xml-options {
      display: none;
    }

    footer {
      background-color: #009390;
      color: white;
      padding: 1em 2em;
      text-align: center;
      margin-top: 3em;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">IPBES</div>
    <nav><!-- Optional: future navigation links --></nav>
  </header>

  <main>
    <h1>SPARQL Query Interface</h1>
    <p>
      Use this interface to run custom SPARQL queries against selected datasets.
      Choose the dataset (graph), format of the results, and view or download your results. 
      This tool is intended for researchers, developers, and data analysts working with IPBES knowledge systems.
      To use the tool: input a query or edit the sample query, set any options and press "Run Query"
      
      You can find the IPBES Ontology here: https://ontology.ipbes.net/report/report.ttl.
    </p>

    <label for="query">SPARQL Query:</label>
    <textarea id="query">
        PREFIX ipbes: &lt;http://ontology.ipbes.net/report&gt;
        SELECT ?g ?gap ?subchapter ?chapter ?description
        WHERE {
        GRAPH ?g {
            ?gap a ipbes:KnowledgeGap .
            OPTIONAL { ?gap ipbes:SubChapter ?subchapter }
            OPTIONAL { ?gap ipbes:hasDescription ?description }
        }
        }
        ORDER BY ?gap
    </textarea>

    <label for="graphUri">Select Target Graph:</label>
    <select id="graphUri">
      <option value="">-- Choose a graph --</option>
      <option value="http://ontology.ipbes.net/graph/ga1">GA1</option>
      <option value="http://ontology.ipbes.net/graph/sua">SUA</option>
      <option value="http://ontology.ipbes.net/graph/va">VA</option>
      <option value="http://ontology.ipbes.net/graph/ias">IAS</option>
    </select>

    <label for="format">Select Export Format:</label>
    <select id="format">
      <option value="application/sparql-results+json">JSON</option>
      <option value="application/sparql-results+xml">XML</option>
      <option value="text/csv">CSV</option>
      <option value="text/tab-separated-values">TSV</option>
      <option value="text/plain">Text</option>
    </select>

    <div id="xml-options">
      <label for="xmlType">XML Output Type:</label>
      <select id="xmlType">
        <option value="xml-to-html">XML to HTML</option>
        <option value="xml-to-html-links">XML to HTML (with links)</option>
        <option value="xml-to-html-plain">Plain XML to HTML</option>
      </select>
    </div>

    <button onclick="runQuery()">Run Query</button>

    <h2>Results</h2>
    <pre id="results"></pre>
  </main>

  <footer>
    <p>© 2025 IPBES secretariat. All rights reserved. | <a href="https://www.ipbes.net/terms-of-use" style="color: #b3d1ff;">Terms of Use</a> | <a href="mailto:mea-ipbes-registration@un.org" style="color: #b3d1ff;">Contact Us</a></p>
  </footer>

  <script>
    const formatSelector = document.getElementById("format");
    const xmlOptions = document.getElementById("xml-options");

    formatSelector.addEventListener("change", () => {
      xmlOptions.style.display = formatSelector.value === "application/sparql-results+xml" ? "block" : "none";
    });

    async function runQuery() {
  const query = document.getElementById("query").value;
  const graphUri = document.getElementById("graphUri").value;
  const format = document.getElementById("format").value;
  const xmlType = document.getElementById("xmlType").value;
  const resultsBox = document.getElementById("results");

  // Clear previous results
  resultsBox.textContent = "";

  // Check for harmful operations
  const harmfulPatterns = [
    /\bDROP\b/i,
    /\bDELETE\b/i,
    /\bINSERT\b/i,
    /\bLOAD\b/i,
    /\bCLEAR\b/i,
    /\bCREATE\b/i,
    /\bCOPY\b/i,
    /\bMOVE\b/i
  ];

  if (harmfulPatterns.some(pattern => pattern.test(query))) {
    resultsBox.textContent = "🚫 Error: Destructive operations like DROP, DELETE, or INSERT are not allowed.";
    return;
  }

  const endpoint = "http://localhost:3030/report/query";
  const params = new URLSearchParams();
  params.append("query", query);
  if (graphUri) {
    params.append("default-graph-uri", graphUri);
  }

  const headers = {
    "Accept": format
  };

  try {
    const res = await fetch(`${endpoint}?${params.toString()}`, { headers });
    const text = await res.text();

    if (!res.ok) {
      throw new Error(`Server returned status ${res.status}`);
    }

    if (format === "application/sparql-results+xml" && xmlType) {
      resultsBox.textContent = `[${xmlType} output]\n\n` + text;
    } else {
      resultsBox.textContent = text;
    }
  } catch (err) {
    resultsBox.textContent = "❌ Query failed: " + err.message;
  }
}

  </script>

</body>
</html>
