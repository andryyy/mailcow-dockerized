<html>
  <head>
  <meta name="x-apple-disable-message-reformatting" />
  <style>
  body {
    font-family: Helvetica, Arial, Sans-Serif;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
  }
  th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
  }
  td.fixed {
    white-space: nowrap;
  }
  th {
    background-color: #56B04C;
    color: white;
  }
  tr:nth-child(even) {
    background-color: #f2f2f2;
  }
  /* mobile devices */
  @media all and (max-width: 480px) {
    .mob {
    display: none;
    }    
  }
  </style>
  </head>
  <body>
    <p>Hi {{username}}!<br>
    {% if counter == 1 %}
    There is 1 new message waiting in quarantine:<br>
    {% else %}
    There are {{counter}} new messages waiting in quarantine:<br>
    {% endif %}
    <table>
    <tr><th>Subject</th><th>Sender</th><th class="mob">Score</th><th class="mob">Arrived on</th>{% if quarantine_acl == 1 %}<th>Actions</th>{% endif %}</tr>
    {% for line in meta %}
    <tr>
    <td>{{ line.subject|e }}</td>
    <td>{{ line.sender|e }}</td>
    <td class="mob">{{ line.score }}</td>
    <td class="mob">{{ line.created }}</td>
    {% if quarantine_acl == 1 %}
    <td class="fixed"><a href="https://{{ hostname }}/qhandler/release/{{ line.qhash }}">release</a> | <a href="https://{{ hostname }}/qhandler/delete/{{ line.qhash }}">delete</a></td>
    {% endif %}
    </tr>
    {% endfor %}
    </table>
    </p>
  </body>
</html>
