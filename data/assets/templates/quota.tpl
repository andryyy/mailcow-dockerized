<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            body {
            font-family: Calibri, Arial, Verdana;
            }
            table {
            border: 0;
            border-collapse: collapse;
            <!--[if mso]>
            border-spacing: 0px;
            table-layout: fixed;
            <![endif]-->
            }
            tr {
            display: flex;
            }
            #progressbar {
            color: #000;
            background-color: #f1f1f1;
            width: 100%;
            }
            {% if (percent >= 95) %}
            #progressbar {
            color: #fff;
            background-color: #FF0000;
            text-align: center;
            width: {{percent}}%;
            }
            {% elif (percent < 95) and (percent >= 80) %}
            #progressbar {
            color: #fff;
            background-color: #FF8C00;
            text-align: center;
            width: {{percent}}%;
            }
            {% else %}
            #progressbar {
            color: #fff;
            background-color: #00B000;
            text-align: center;
            width: {{percent}}%;
            }
            {% endif %}
            #graybar {
            background-color: #D8D8D8;
            width: {{100 - percent}}%;
            }
            a:link, a:visited {
            color: #858585;
            text-decoration: none;
            }
            a:hover, a:active {
            color: #4a81bf;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <td colspan="2">
                    Hi {{username}}!<br /><br />
                    Your mailbox is now {{percent}}% full, please consider deleting old messages to still be able to receive new mails in the future.<br /><br />
                </td>
            </tr>
            <tr>
                <td id="progressbar">{{percent}}%</td>
                <td id="graybar"></td>
            </tr>
        </table>
    </body>
</html>
