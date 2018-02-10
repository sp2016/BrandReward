<html>
<head>
    <style>
        table.gridtable {
            font-family: verdana, arial, sans-serif;
            font-size: 11px;
            color: #333333;
            border-width: 1px;
            border-color: #666666;
            border-collapse: collapse;
        }

        table.gridtable th {
            border-width: 1px;
            padding: 8px;
            border-style: solid;
            border-color: #666666;
            background-color: #dedede;
        }

        table.gridtable td {
            border-width: 1px;
            padding: 8px;
            border-style: solid;
            border-color: #666666;
            text-align: center;
            background-color: #ffffff;
        }
    </style>
</head>
<body>
<h4>
    This table contain the data about publishers whose siteType is not consistent with core business data，please check it!
</h4>
<div style="width: 667px; height: 220px;" id="newText" name="newText"  class="tablescode">
    <table class="gridtable">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Manager</th>
            <th>Core business</th>
            <th>Site Type</th>
        </tr>
        <?php foreach ($data as $item) { ?>
            <tr>
                <td><?php echo $item['ID'] ?></td>
                <td><?php echo $item['Name'] ?></td>
                <td><?php echo $item['Email'] ?></td>
                <td><?php echo $item['Manager'] ?></td>
                <td><?php echo $item['SiteTypeNew'] ?></td>
                <td><?php echo $item['SiteOption'] ?></td>
            </tr>
        <?php }?>
    </table>
</div>
</body>
</html>

