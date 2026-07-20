<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Liste des etudiants</h1>
    <?php foreach($etudiants as $etudiant) {?>
    <li><?php echo $etudiant['Nom'];?></li>
    <li><?php echo $etudiant['age'];?></li>
    <?php } ?>
</body>
</html>