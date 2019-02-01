<?php

function validate($data){
	$data = trim($data);
  	$data = stripslashes($data);
  	$data = htmlspecialchars($data);
  	$data = str_replace('\\', '', $data);
  	$data = str_replace('/', '', $data);
  	$data = str_replace("'", '', $data);
  	$data = str_replace(";", '', $data);
  	$data = str_replace("(", '', $data);
  	$data = str_replace(")", '', $data);
  	return $data;
}

session_start();

//Handle Changing Projects
if(isset($_POST['project-id'])){

  $newproject = $_POST['project-id'];
  //~~JOSH~~
  //Need checking that the user really has this project here
  //Prevents client-side editing of project value to access those of other orgs
  //@Tom
  
  $_SESSION['project'] = $newproject;

}

//Handle Changing Workspaces
if(isset($_POST['workspace-id'])){

  $newworkspace = $_POST['workspace-id'];
  //~~JOSH~~
  //Need checking that the user really has this workspace here
  //Prevents client-side editing of workspace value to access those of other orgs
  //@Tom
  
  $_SESSION['workspace'] = $newworkspace;
  $_SESSION['project'] = null;

}

if(isset($_POST['task-name'])){

  $taskname = validate($_POST['task-name']);
  $taskdesc = validate($_POST['task-desc']);
  $thisuser = $_SESSION['id'];

  require('../php/connect.php');
  $query = "INSERT INTO tasks (name, description, creator, date) VALUES ('$taskname','$taskdesc','$thisuser',now())";
  $result = mysqli_query($link,$query);
  if (!$result){
      die('Error: ' . mysqli_error($link));
  }
  mysqli_close($link);

  $fmsg = "Successfully Created Task!";

}

if(isset($_POST['goal-name'])){

  $goalname = validate($_POST['goal-name']);
  $goalvalue = validate($_POST['goal-value']);
  $thisproject = $_SESSION['project'];
  $thisuser = $_SESSION['id'];

  require('../php/connect.php');
  $query = "INSERT INTO goals (name, creator, date, project, value) VALUES ('$goalname','$thisuser',now(),'$thisproject','$goalvalue')";
  $result = mysqli_query($link,$query);
  if (!$result){
      die('Error: ' . mysqli_error($link));
  }
  mysqli_close($link);

  $fmsg = "Successfully Created Goal!";

}

if(!isset($_SESSION['username'])){

	header('Location: ../index.php');

}else{

?>

<!DOCTYPE html>

<head>
	<!-- Global site tag (gtag.js) - Google Analytics ~ Will go here-->
		
	<!-- Bootstrap, cause it's pretty hecking neat. Plus we have it locally, cause we're cool -->
	<link rel="stylesheet" href="../bootstrap-4.1.0/css/bootstrap.min.css">
    <script src="../js/jquery.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../bootstrap-4.1.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i" rel="stylesheet">
	
	<title>
		ProjeX
	</title>

	<!-- Import our CSS -->
	<link href="../css/main.css" rel="stylesheet" type="text/css" />

	<!-- Mobile metas -->
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>

<body>
<!-- Navbar -->
  <nav class="navbar navbar-dark bg-grey pb_bottom">
      <span id="openNavButton" style="font-size:30px;cursor:pointer;color:white;padding-right:30px;" onclick="toggleNav()">&#9776;</span>
      <a class="nav-link" href="../php/logout.php">Logout</a>
  </nav>

<!--Spooky stuff in the middle-->
  <div class="container-fluid">
    <div class="row">
      <div id="mySidenav" style="padding-right:0; padding-left:0;" class="sidenav bg-grey">
        <nav style="width:100%;" class="navbar navbar-dark">
          <div class="container" style="padding-left:0px;">
          <ul class="nav navbar-nav align-top">
           <a class="navbar-brand icon" href="#"><img src="../imgs/workspacePlaceholder.png" alt="icon" width="60" height="60">Projex</a>
           <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Workspaces
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                <?php

                require('../php/connect.php');

                $username = $_SESSION['username'];
            $query = "SELECT workspaces.name, workspaces.id FROM ((user_workspace_mapping INNER JOIN workspaces ON workspaces.id = user_workspace_mapping.workspace) INNER JOIN users ON user_workspace_mapping.user = users.id) WHERE users.username = '$username'";
            $result = mysqli_query($link, $query);
            if (!$result){
              die('Error: ' . mysqli_error($link));
            }
            while($resultArray = mysqli_fetch_array($result)){
            $workspaceName = $resultArray['name'];
            $workspaceID = $resultArray['id'];

                ?>
                <form method="POST"><input type="hidden" value="<?php echo $workspaceID; ?>" name="workspace-id"/><input class="dropdown-item" type="submit" value="<?php echo $workspaceName; ?>"></form>
                <?php } ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="workspace.php">Create New</a>
              </div>
              <hr class="sidenavHR">
              <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="metrics.php">Metrics</a>
            <a class="nav-link active" href="backlog.php">Backlog</a>
            <a class="nav-link" href="active.php">Active</a>
            <a class="nav-link" href="docs.php">Docs</a>
            <a class="nav-link" href="messages.php">Messages</a>
            <hr class="sidenavHR">
            <a class="nav-link" href="account.php">My Account</a>
            <a class="nav-link" href="organization.php">My Organization</a>
          </ul>
          </div>
        </nav>
      </div>
      <div id="pageBody">
      <div class="row">
      	<div class="col-12">
          <?php if(isset($fmsg)){ echo "<div class='card'><p>" . $fmsg . "</p></div>"; } ?>
      		<h1>Backlog</h1>	
      	</div>
        <div class="col-sm-6">
          <div class="dropdown">
              <div class="btn-group">
                <button type="button" class="btn btn-secondary"><?php 
                  require('../php/connect.php');
                  $project = $_SESSION['project'];
                  $query = "SELECT name FROM projects WHERE id='$project'";
                  $result = mysqli_query($link, $query);
                  if (!$result){
                    die('Error: ' . mysqli_error($link));
                  }
                  list($name) = mysqli_fetch_array($result);
                  if($_SESSION['project'] == null || $_SESSION['workspace'] == null){
                    echo "Select a Project";
                  }
                  else{
                    echo $name;
                  }
                ?></button>
                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
              <div class="dropdown-menu">

                <?php

                require('../php/connect.php');

                $username = $_SESSION['username'];
                $workspace = $_SESSION['workspace'];
                $query = "SELECT projects.name, projects.id FROM ((user_project_mapping INNER JOIN projects ON projects.id = user_project_mapping.project) INNER JOIN users ON user_project_mapping.user = users.id) WHERE users.username = '$username' AND projects.workspace = '$workspace'";
                $result = mysqli_query($link, $query);
                if (!$result){
                  die('Error: ' . mysqli_error($link));
                }
                while($resultArray = mysqli_fetch_array($result)){
                $projectName = $resultArray['name'];
                $projectID = $resultArray['id'];

                ?>
                <form method="POST"><input type="hidden" value="<?php echo $projectID; ?>" name="project-id"/><input class="dropdown-item <?php if($_SESSION['project'] == $projectID){ echo 'active'; } ?>" type="submit" value="<?php echo $projectName; ?>"></form>
                <?php } ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="project.php">Create New</a>
              </div>
            </div>
            </div>
        <div class="card">
        <h3>Backlog Overview</h3>

        </div>
        <div class="card">
        <h3>Create A Task</h3>
        <form method="POST" class="">
				  <div class="form-row">
				    <div class="form-group col-md-12">
				      <label for="task-name">Task Name</label>
				      <input type="text" maxlength="90" class="form-control" id="task-name" name="task-name" placeholder="Enter a task name">
				    </div>
				  </div>
				  <div class="form-row">
				    <div class="form-group col-md-12">
				      <label for="task-desc">Task Description</label>
				      <textarea maxlength="450" type="text" class="form-control" id="task-desc" name="task-desc" placeholder="Enter the task's description"></textarea>
				  </div>
				  </div>
				  <button type="submit" class="btn btn-primary">Submit</button>
				</form>
        </div>
        <div class="card">
        <h3>Create A Goal</h3>
				<form method="POST" class="pt-4">
				  <div class="form-row">
				    <div class="form-group col-md-12">
				      <label for="goal-name">Goal Name</label>
				      <input type="text" maxlength="90" class="form-control" id="goal-name" name="goal-name" placeholder="Enter a goal name">
				    </div>
				  </div>
				  <div class="form-row">
				    <div class="form-group col-md-12">
				      <label for="goal-value">Goal Value</label>
				      <br>
              <small>An integer representing the team's relative weighted value of completing this goal.</small>
              <br>
              <input type="number" class="form-control" name="goal-value" id="goal-value" value="1" />
				  </div>
				  </div>
				  <button type="submit" class="btn btn-primary">Submit</button>
				</form>
        </div>
        </div>
        <div class="col-sm-6">
          <?php

            require('../php/connect.php');

            $username = $_SESSION['username'];
            $activeProject = $_SESSION['project'];

            $query = "SELECT goals.name, goals.id, goals.value FROM goals WHERE goals.project = '$activeProject' AND goals.status='backlog'";
            $result = mysqli_query($link, $query);
            if (!$result){
              die('Error: ' . mysqli_error($link));
            }
            while($resultArray = mysqli_fetch_array($result)){
            $goalName = $resultArray['name'];
            $goalID = $resultArray['id'];
            $goalValue = $resultArray['value'];

          ?>
          <div class="head">
            <h4 style=" float:left;"><?php echo $goalName; ?></h4><h4 style="float:right;"><?php echo $goalValue; ?></h4>
          </div>
            <?php

            require('../php/connect.php');

            $username = $_SESSION['username'];
            $activeProject = $_SESSION['project'];

            $query = "SELECT tasks.name, tasks.description, tasks.creator, tasks.date FROM tasks WHERE tasks.id IN (SELECT task FROM goal_task_mapping WHERE goal = '$goalID') AND tasks.status='backlog'";
            $result2 = mysqli_query($link, $query);
            if (!$result2){
              die('Error: ' . mysqli_error($link));
            }
            while($taskArray = mysqli_fetch_array($result2)){
            $taskName = $taskArray['name'];
            $taskID = $taskArray['id'];
            $taskDesc = $taskArray['description'];
            $taskCreator = $taskArray['creator'];
            $taskDate = $taskArray['date'];

          ?>
          <div class="card">
            <h4><?php echo $taskName; ?></h4>
            <hr>
            <p><?php echo $taskDesc; ?></p>
            <br>
            <small>Created By : <?php
              require('../php/connect.php');
              $query = "SELECT firstname, lastname FROM users WHERE id = '$taskCreator'";
              $result3 = mysqli_query($link, $query);
              if (!$result3){
                die('Error: ' . mysqli_error($link));
              }
              list($firstname, $lastname) = mysqli_fetch_array($result3);
              echo $firstname . " " . $lastname;
            ?> on <?php echo $taskDate; ?></small>
          </div>
          <?php
          }
          ?>
          <?php
          }
          ?>
        </div>
        </div>
    </div>
    <footer class="bg-grey color-white pb_top">
      <center><p>
        Team 2004-901, 2019, All Rights Reserved
      </p></center>
    </footer>
    </div>
    </div>
</body>

<script src="../js/scripts.js" type="text/javascript"></script>

</html>

<?php 

}

?>