<?PHP
session_start();

$cnx = @mysqli_connect('db', 'root', 'root', 'football');

if ($cnx === false) {
    exit("Connexion impossible");
}

$allMatchesQuery = "SELECT m.local_team, m.away_team, local_score, away_score FROM `match` as m";

if(isset($_POST['q']) && !empty($_POST['q'])){
    $team = $_POST['q'];
    $allMatchesQuery .= " INNER JOIN `team` as t on t.id = m.local_team OR t.id = m.away_team WHERE t.label LIKE '%$team%'";
}

$request = mysqli_query($cnx, $allMatchesQuery);

?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>⚽ Football</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/master.css" rel="stylesheet">
</head>

<body>

<div class="d-flex justify-content-center mt-5 mb-4">
    <h1>⚽ Football</h1>
</div>

<div class="container">
<a href="/" class="btn btn-primary ml-2 mb-3">Show Teams</a>
    <div class="d-flex justify-content-center mb-3">
        <form method="POST">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
                <input type="search" name="q" class="form-control" placeholder="Team's name and not id" aria-label="nom équipe" aria-describedby="basic-addon1">
            </div>
        </form>
    </div>    
    <div class="list-group">
        <?php while($match = mysqli_fetch_assoc($request) ) : ?>
            <div class="d-flex justify-content-center list-group-item list-group-item-action">
                <?php printf("<b>%s</b>&nbsp;%s - %s&nbsp;<b>%s</b>", $match['local_team'], $match['local_score'], $match['away_score'], $match['away_team'])  ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>