<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="UTF-8"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>Clubes</title>
        <link rel="stylesheet" 
              href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
          <![CDATA[
            $(document).ready(function () {
              $('#tablaClubes').DataTable();
            });
          ]]>
        </script>
      </head>
      <body>
        <h2>Clubes del Mundo</h2>
        <table id="tablaClubes" class="display">
          <thead>
            <tr>
              <th>CIF</th>
              <th>Nombre</th>
              <th>País</th>
              <th>Color</th>
            </tr>
          </thead>
          <tbody>
            <xsl:for-each select="clubes/club">
              <tr>
                <td><xsl:value-of select="@CIF" /></td>
                <td><xsl:value-of select="nombre" /></td>
                <td><xsl:value-of select="pais" /></td>
                <td><xsl:value-of select="color"/></td>
              </tr>
            </xsl:for-each>
          </tbody>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>