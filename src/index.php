<?php 
session_start();

if (!isset($_SESSION['favoris'])) {
    $_SESSION['favoris'] = [];
}

$cnx = @mysqli_connect('db', 'root', 'root', 'football');

if ($cnx === false) {
    exit("Connexion impossible");
}

$getAllTeamsQuery = "SELECT * FROM team";

$resultTeams = mysqli_query($cnx, $getAllTeamsQuery);

if(isset($_POST['q']) && ($_POST['q'] ==! null)){
    $q = $_POST['q']; 
    $getAllTeamsQuery .= " WHERE label LIKE '%$q%'";
}

$result = mysqli_query($cnx, $getAllTeamsQuery);

if ($results === false) {
    exit("Requête impossible");
}

$addFavoris = isset($_GET['action'], $_GET['teamId']) && $_GET['action'] === 'favori';
$deleteFromFavoris = isset($_GET['action'], $_GET['delete']) && $_GET['action'] == 'favori';
$addMatch = isset($_POST['match']);

if($addFavoris){
    $teamId = $_GET['teamId'];
    $getTeamQuery = "SELECT * FROM team WHERE id = '$teamId'";
    $resultTeam = mysqli_query($cnx, $getTeamQuery);

    if(!$resultTeam->fetch_row()){
        $errorTeamMessage = "Cette Equipe n'existe pas !";
    } else if(in_array($teamId, $_SESSION['favoris'])){
        $errorTeamMessage = "Cette Equipe a déjà été ajouté dans vos favoris !";
    } else {
        array_push($_SESSION['favoris'], $teamId);
        header('location: /'); // Pour supprimer les variables GET dans l'adresse url
    }
}

if($deleteFromFavoris){
    $deletedTeam = $_GET['delete'];
    $teamIndex = array_search($deletedTeam, $_SESSION['favoris']);
    if($teamIndex === false){
        $message = "L'équipe que vous voulez supprimer n'éxsite pas !";
    } else {
        array_splice($_SESSION['favoris'], $teamIndex, 1);
        header('location: /'); // Pour supprimer les variables GET dans l'adresse url
    }
}

if($addMatch){
    $match = $_POST['match'];
    if($match['localTeam'] === $match['awayTeam']){
        $errorMatchMessage = "<b>Local Team</b> et <b>Away Team</b> ne doivent pas être identiques !";
    } else {
        $localTeam = $match['localTeam'];
        $awayTeam = $match['awayTeam'];
        $localScore = $match['localScore'];
        $awayScore = $match['awayScore'];
        $insertMatchQuery = "INSERT INTO `match` VALUES(NULL, '$localTeam', '$awayTeam', $localScore, $awayScore)";
        if(mysqli_query($cnx, $insertMatchQuery) === true){
            $matchAddedMessage = "Le match a bien été ajouté !";
        } else {
           printf("Error message: %s\n", mysqli_error($cnx));
        }
    }
}

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
?>
<!DOCTYPE html>
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
<a href="matches.php" class="btn btn-primary ml-2 mb-3">Show Matches</a>
    <div class="d-flex justify-content-center mb-3">
        <form method="POST">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
                <input type="search" name="q" class="form-control" placeholder="Nom d'équipe" aria-label="Nom d'équipe" aria-describedby="basic-addon1">
            </div>
        </form>
        <form method="POST" class="ml-3 d-flex">
            <div class="form-group">
                <!-- <label for="exampleFormControlSelect1">Example select</label> -->
                <select class="form-control" name="match[localTeam]" id="exampleFormControlSelect1" required>
                <option value="">--Local Team--</option>
                <?php  while ($teams = mysqli_fetch_assoc($resultTeams)) : ?>
                    <option><?= $teams['id'] ?></option>
                <?php
                endwhile;
                mysqli_data_seek($resultTeams, 0);
                ?>
                </select>
            </div>
            
            <div class="form-group ml-2">
                <!-- <label for="exampleFormControlSelect1">Example select</label> -->
                <select class="form-control" name="match[awayTeam]" id="exampleFormControlSelect1" required>
                <option value="">--Away Team--</option>
                <?php  while ($teams = mysqli_fetch_assoc($resultTeams)) { ?>
                    <option><?= $teams['id'] ?></option>
                <?php } ?>
                </select>
            </div>
            <div class="form-group ml-2">
                <!-- <label for="exampleFormControlInput1">Email address</label> -->
                <input type="number" name="match[localScore]" class="form-control" id="exampleFormControlInput1" placeholder="Local Score" required>
            </div>
            <div class="form-group ml-2">
                <!-- <label for="exampleFormControlInput1">Email address</label> -->
                <input type="number" name="match[awayScore]" class="form-control" id="exampleFormControlInput1" placeholder="Away Score" required>
            </div>
            <button type="submit" class="btn btn-primary ml-2 mb-3">Add Match</button>
        </form>
    </div>
    <?php if(isset($errorTeamMessage)) : ?>
        <div class="alert alert-warning" role="alert">
            <?= $errorTeamMessage ?>
        </div>
    <?php endif;?>
    <?php if(isset($errorMatchMessage)) : ?>
        <div class="alert alert-warning" role="alert">
            <?= $errorMatchMessage ?>
        </div>
    <?php endif;?>
    <?php if(isset($matchAddedMessage)) : ?>
        <div class="alert alert-success" role="alert">
            <?= $matchAddedMessage ?>
        </div>
    <?php endif;?>
    <div class="list-group">
        <?php while ($team = mysqli_fetch_assoc($result)) : ?>
            <div class="d-flex justify-content-start list-group-item list-group-item-action">
                <?php if(!in_array($team['id'], $_SESSION['favoris'])) : ?> 
                    <a href="?action=favori&teamId=<?= $team['id'] ?>">
                        <i class="bi bi-star mr-3"></i>
                    </a>
                <?php else : ?>
                    <a href="?action=favori&delete=<?= $team['id'] ?>">    
                        <i class="bi bi-star-fill mr-3" style='color: #e6ba78;'></i>
                    </a>
                <?php endif; ?>
                <img height="45" width="45" src="images/<?= $team['flag'] ?>" alt="">
                <?= $team['label'] ?>
            </div>
        <?php endwhile; ?>    
    </div>
</div>
<!-- Bootstrap core JavaScript -->
<script src="js/jquery.min.js"></script>
<script src="js/index.js"></script>

</body>

</html>
<?php mysqli_close($cnx); ?>
