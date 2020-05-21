<html>
   <head>
      <style>
         body {
         font-family: sans-serif;
         }
         div {
         /* width based on text fill*/
         display: inline-block;
         }
         #progressbar {
         color: #000;
         background-color: #f1f1f1;
         width: 100%;
         }
         {% if (percent >= 95) %}
         #progressbar > div {
         color: #fff;
         background-color: #FF0000;
         text-align: center;
         padding: 0.01em;
         width: {{percent}}%;
         }
         {% elif (percent < 95) and (percent >= 80) %}
         #progressbar > div {
         color: #fff;
         background-color: #FF8C00;
         text-align: center;
         padding: 0.01em;
         width: {{percent}}%;
         }
         {% else %}
         #progressbar > div {
         color: #fff;
         background-color: #00B000;
         text-align: center;
         padding: 0.01em;
         width: {{percent}}%;
         }
         {% endif %}
      </style>
   </head>
   <body>
      <div>
         <p>Hi {{username}}!<br><br>
            Your mailbox is now {{percent}}% full, please consider deleting old messages to still be able to receive new mails in the future.<br>
         <div id="progressbar">
            <div>{{percent}}%</div>
         </div>
         </p>
      </div>
   </body>
</html>
