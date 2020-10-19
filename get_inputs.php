
<html>
<body>  

<?php
// define variables and set to empty values
$age = "";
$BMI = "";
$diabetes = "";
$Haemor = "";
$Ulength = "";
$HRQOL = "";
$psa = "";
$tstage = "";
$PGG = "";
$SGG = "";
$Pbiopsy = "";
$Nbiopsy = "";
$ASA = "";
$ACoag = "";
$Nerve = "";
$adt = "";
$abdom = "";
$pelvic = "";
$trigone = "";
$rectum = "";
$v75 = "";
$BED = "";
$adtL = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $age = test_input($_POST["age"]);
  $BMI = test_input($_POST["BMI"]);
  $Ulength = test_input($_POST["Ulength"]);
  $diabetes = test_input($_POST["diabetes"]);
  $Haemor = test_input($_POST["Haemor"]);
  $HRQOL = test_input($_POST["HRQOL"]);
  $psa = test_input($_POST["psa"]);
  $tstage = test_input($_POST["tstage"]);
  $PGG = test_input($_POST["PGG"]);
  $SGG = test_input($_POST["SGG"]);
  $Pbiopsy = test_input($_POST["Pbiopsy"]);
  $Nbiopsy = test_input($_POST["Nbiopsy"]);
  $ASA = test_input($_POST["ASA"]);
  $ACoag = test_input($_POST["ACoag"]);
  $Nerve = test_input($_POST["Nerve"]);
  $adt = test_input($_POST["adt"]);
  $adtL = test_input($_POST["adtL"]);
  $abdom = test_input($_POST["abdom"]);
  $pelvic = test_input($_POST["pelvic"]);
  $trigone = test_input($_POST["trigone"]);
  $rectum = test_input($_POST["rectum"]);
  $v75 = test_input($_POST["v75"]);
  $BED = test_input($_POST["BED"]);
  
  
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<h2>ProRaDS: Prostatectomy vs Radiotherapy Decision Support tool</h2>

<form action="Display_results.php" method="post">
<h3>Patient Properties:</h3>
Age: <input type="number" name="age" min="30" max="90" required="yes" value=<?php echo $age?> > years<br><br>
BMI: <input type="number" name="BMI" min="20" max="50" required="yes" value=<?php echo $BMI?>> kg/m2<br><br>
Uretra length: <input type="number" name="Ulength" min="3" max="27" required="yes" value=<?php echo $Ulength?>> mm<br><br>
Diabetes: <input type="radio" name="diabetes" required="yes" <?php if (isset($diabetes) && $diabetes==1) echo "checked";?> value=1>yes
  <input type="radio" name="diabetes" required="yes" <?php if (isset($diabetes) && $diabetes==0) echo "checked";?> value=0>no <br><br>
Haemorrhoids: <input type="radio" name="Haemor" required="yes" <?php if (isset($Haemor) && $Haemor==1) echo "checked";?> value=1>yes
  <input type="radio" name="Haemor" required="yes" <?php if (isset($Haemor) && $Haemor==0) echo "checked";?> value=0>no <br><br>
Pre-treatment erectile function: <input type="number" name="HRQOL" min="0" max="100" required="yes" value=<?php echo $HRQOL?>> %<br><br>

<h3>Disease Properties: </h3>
PSA level: <input type="number" name="psa" min="0" max="50" required="yes" value=<?php echo $psa?>> ng/ml<br><br>
T-stage: <input type="radio" name="tstage" required="yes" <?php if (isset($tstage) && $tstage==0) echo "checked";?> value=0>T1
  <input type="radio" name="tstage" <?php if (isset($tstage) && $tstage==1) echo "checked";?> value=1>T2 <br><br>
Primary Gleason Grade: <input type="radio" name="PGG" required="yes" <?php if (isset($PGG) && $PGG==0) echo "checked";?> value=0>3
  <input type="radio" name="PGG" <?php if (isset($PGG) && $PGG==1) echo "checked";?> value=1>4 <br><br>
Secondary Gleason Grade:<input type="radio" name="SGG" required="yes" <?php if (isset($SGG) && $SGG==0) echo "checked";?> value=0>3
  <input type="radio" name="SGG" <?php if (isset($SGG) && $SGG==1) echo "checked";?> value=1>4 <br><br>
Number of positive biopsy cores: <input type="number" name="Pbiopsy" min="1" max="10" step="1" required="yes" value=<?php echo $Pbiopsy?>><br><br>
Number of negative biopsy cores: <input type="number" name="Nbiopsy" min="0" max="10" step="1" required="yes" value=<?php echo $Nbiopsy?>><br><br>
ASA score: <input type="radio" name="ASA" required="yes" <?php if (isset($ASA) && $ASA==1) echo "checked";?> value=1>I/II
  <input type="radio" name="ASA" <?php if (isset($ASA) && $ASA==0) echo "checked";?> value=0>III/IV <br><br>

<h3>Treatment Properties: </h3>
Anticoagulants: <input type="radio" name="ACoag" required="yes" <?php if (isset($ACoag) && $ACoag==1) echo "checked";?> value=1>yes
  <input type="radio" name="ACoag" <?php if (isset($ACoag) && $ACoag==0) echo "checked";?> value=0>no <br><br>
Nerve Sparing surgery: <input type="radio" name="Nerve" required="yes" <?php if (isset($Nerve) && $Nerve==1) echo "checked";?> value=1>yes
  <input type="radio" name="Nerve" <?php if (isset($Nerve) && $Nerve==0) echo "checked";?> value=0>no <br><br>
Androgen deprivation therapy (ADT): <input type="radio" name="adt" required="yes" <?php if (isset($adt) && $adt==1) echo "checked";?> value=1>yes
  <input type="radio" name="adt" <?php if (isset($adt) && $adt==0) echo "checked";?> value=0>no <br><br>
ADT length: <input type="number" name="adtL" min="0" max="12" value=<?php echo $adtL?>> months (if no ADT is given, fill in 0)<br><br>
Prior abdominal surgery: <input type="radio" name="abdom" required="yes" <?php if (isset($abdom) && $abdom==1) echo "checked";?> value=1>yes
  <input type="radio" name="abdom" <?php if (isset($abdom) && $abdom==0) echo "checked";?> value=0>no <br><br>
Irradiation of Pelvic nodes: <input type="radio" name="pelvic" required="yes" <?php if (isset($pelvic) && $pelvic==1) echo "checked";?> value=1>yes
  <input type="radio" name="pelvic" <?php if (isset($pelvic) && $pelvic==0) echo "checked";?> value=0>no <br><br>
Mean Trigone dose: <input type="number" name="trigone" min="0" max="100" required="yes" value=<?php echo $trigone?> > Gray<br><br>
Mean Rectum dose: <input type="number" name="rectum" min="0" max="100" required="yes" value=<?php echo $rectum?> > Gray<br><br>
Rectum volume receiving at least 75 Gray: <input type="number" name="v75" min="0" max="100" required="yes" value=<?php echo $v75?> > %<br><br>
Biolocally equivalent dose: <input type="number" name="BED" min="0" max="200" required="yes" value=<?php echo $BED?> > Gray<br><br>

<input type="submit">
</form>


</body>
</html> 

    
