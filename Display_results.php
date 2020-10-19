
<html>
<head>
<style>
table, th, td {
    border: 1px solid black;
}
</style>
</head>
<body>  
<?php
// define variables and set to empty values
$age = $BMI = $Ulength = $HRQOL = $psa = $Pbiopsy = $Nbiopsy = $trigone = $rectum = $v75 = $BED = "";
$adtL = 0;

$P1 = "";
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
<h2>Results ProRaDS</h2>
<?php
// get model parameters
$N_c = 240;   // Number of cycles
$t_C = 1;     // Cycle time in months
$DR_c = 0.04; // discount rate costs
$DR_u = 0.015;// discount rate utility

// get costs
$Surgery_costs = 9780;  // costs of prostatectomy
$RT_costs      = 8275;  // costs of radiotherapy treatment

// anual costs
$Hc_a12  = 511.2;  // costs healthy lifestate first two years
$Hc_a3   = 255.6;  // costs healthy lifestate after two years
$Rc_a    = 511.2;  // costs recurence
$Mc_a1   = 2288; // costs metastasis 
$Mc_a2   = 6493;   // costs metastasis castrate resistant
$Tc_R_a  = 94.5;   // rectal toxicity
$Tc_U_a  = 302.5;  // urinary toxicity
$Tc_E_a  = 60;     // erectile dysfunction

// Utility values

$Hu      = 0.99; // utility healthy lifestate
$Ru      = 0.76; // utility recurence
$Mu      = 0.11; // utility metastasis
$Tu_R    = 0.79; // rectal toxicity
$Tu_U    = 0.90; // urinary toxicity
$Tu_E    = 0.91; // erectile dysfunction
$Tu_RU   = 0.76; // rectal+urinary toxicity
$Tu_RE   = 0.61; // rectal toxicity+erectile dysfunction
$Tu_UE   = 0.87; // urinary toxicity+erectile dysfunction
$Tu_RUE  = 0.45; // rectal+urinary toxicity+erectile dysfunction

//RADIOTHERAPY
// Toxicity probabilities
if ($psa<4) $temp = 1; else $temp = 0;
$EF_RT = 1/(1+exp(-(-5.22+0.54/10*$HRQOL+1.17*$temp+1.18*(1-$adt)))); $P1 = $EF_RT; //erectile function
$P2a = 1/(1+exp(-(-2.489-0.47*$ACoag+0.24*$diabetes+0.41*$Haemor+0.45*$pelvic-0.43*$adt+0.034*$rectum)));  //acute rectal bleeding
$R_RT = 1/(1+exp(-(-3.5082+0.0258*$P2a*100+0.75*$abdom+0.0571*$v75))); $P2 = 1-$R_RT/$P2a; // late rectal bleeding
$UI_RT = 1/(1+exp(-(-9.67+0.1015*$trigone))); $P3 = $UI_RT; //urinary incontinence
$TCP_RT = exp(-exp(1.19-0.0106*$adtL+0.446*($PGG+$SGG)-0.0212*$BED+0.04156*$psa)); $P_Rec = (1-$TCP_RT)+(1-$TCP_RT)*0.05;//tumor control probability

// Define initial population numbers
$N_Tr[0] = $N_Tu[0] = $N_Tru[0] = $N_healthy[0] = $N_rec[0] = $N_met1[0] = $N_met2[0] = $N_dead[0] = 0;

// All patients start with erectile dysfunction, and a percentage start with urinary incontinence and rectal bleeding
$N_Te[0] = 1*(1-$P2a)*(1-$P3); 
$N_Tre[0] = 1*(1-$P3)*$P2a;
$N_Teu[0] = 1*(1-$P2a)*$P3;
$N_True[0] = 1*$P2a*$P3;

$Baseline = 1.013-0.0038*$age;
$QALY = 1/12*$Baseline*($N_Te[0]*$Tu_E+$N_Tre[0]*$Tu_RE+$N_Teu[0]*$Tu_UE+$N_True[0]*$Tu_RUE);
$Costs = $RT_costs+$N_Te[0]*($Tc_E_a+$Hc_a12)+$N_Tre[0]*($Tc_R_a+$Tc_E_a+$Hc_a12)+$N_Teu[0]*($Tc_U_a+$Tc_E_a+$Hc_a12)+$N_True[0]*($Tc_R_a+$Tc_E_a+$Tc_U_a+$Hc_a12);

// start the markov chain
for ($i = 1; $i <= 240; $i++) {
  // calculate time dependant individual transition probabilities for this cycle
  if ($i <= 24) {$T1 = Calculate_transition($P1,24,$i);}else{$T1 = 0;};
  if ($i <= 36) {$T2 = Calculate_transition($P2,36,$i);}else{$T2 = 0;};
  
  // you cannot recover from urinary incontinence after RT in this model 
  // from all toxicities
  $TT3  = $T1*$T2;  // to urinary incontinence
  $TT23 = $T1-$TT3;  // to rectal bleeding and urinary incontinence
  $TT13 = $T2-$TT3;  // to urinary incontinence and erectile function
  
  // Erectile dysfunction and Rectal bleeding
  $T12H  = $T1*$T2;  // to healthy
  $T12T1 = $T2-$T12H; // to Erectile dysfuntion
  $T12T2 = $T1-$T12H; // to Rectal bleeding
  
  // Erectile dysfunction and Urinary incontinence 
  $T13T3 = $T1; // to Urinary incontinence
  
  // Rectal bleeding and Urinary incontinenceT
  $T23T3 = $T2; // to Urinary incontinence
  
  $T1H = $T1; // Erectile dysfunction to healthy
  $T2H = $T2; // Rectal bleeding to healthy
  
  // recurrence and metastatic disease
  $HR = (($P_Rec-$P_Rec*exp(-0.05*($i+1)))-($P_Rec-$P_Rec*exp(-0.05*($i))))/(1-($P_Rec-$P_Rec*exp(-0.05*($i+1))));
  //echo $HR; echo "<br>";
  $RM = ((0.84-0.84*exp(-0.05*($i+1)))-(0.84-0.84*exp(-0.05*$i)))/(1-(0.84-0.84*exp(-0.05*($i+1))));
  $MM = 1-exp(1/24*log(1-0.5));
  $MD = ((0.9-0.9*exp(-0.062*($i+1)))-(0.9-0.9*exp(-0.062*$i)))/(1-(0.9-0.9*exp(-0.062*($i+1))));
  
  // chance of non-cancer related death
  $P_D_year = 1.168e-05*exp( 0.1062*round($age+$i/12));
  if ($P_D_year >= 1){$P_D_year = 0.995;};
  $D  = 1-exp(1/12*log(1-$P_D_year));
  
  // calculate number of patients in each health state
  //Healthy
  $N_healthy[$i] = $N_healthy[$i-1]*(1-$HR-$D) + $N_Tre[$i-1]*$T12H +$N_Te[$i-1]*$T1H + $N_Tr[$i-1]*$T2H;
  
  //Toxicity
  $N_Te[$i] = $N_Te[$i-1]*(1-$T1H-$HR-$D) + $N_Tre[$i-1]*$T12T1;
  $N_Tr[$i] = $N_Tr[$i-1]*(1-$T2H-$HR-$D) + $N_Tre[$i-1]*$T12T2;
  $N_Tu[$i] = $N_Tu[$i-1]*(1-$HR-$D) + $N_True[$i-1]*$TT3 + $N_Teu[$i-1]*$T13T3 + $N_Tru[$i-1]*$T23T3;
  
  
  $N_Tre[$i] = $N_Tre[$i-1]*(1-$T12H-$T12T1-$T12T2-$HR-$D);
  $N_Tru[$i] = $N_Tru[$i-1]*(1-$T23T3-$HR-$D)+ $N_True[$i-1]*$TT23;
  $N_Teu[$i] = $N_Teu[$i-1]*(1-$T13T3-$HR-$D)+ $N_True[$i-1]*$TT13;
  
  $N_True[$i] = $N_True[$i-1]*(1-$TT3-$TT13-$TT23-$HR-$D);
  
  //Recurrence
  $N_rec[$i] = $N_rec[$i-1]*(1-$RM-$D)+$HR*($N_healthy[$i-1]+$N_Tr[$i-1]+$N_Tu[$i-1]+$N_Te[$i-1]+$N_Tru[$i-1]+$N_Tre[$i-1]+$N_Teu[$i-1]+$N_True[$i-1]);
  
  //Metastatic disease
  $N_met1[$i] = $N_met1[$i-1]*(1-$MM-$MD-$D)+$N_rec[$i-1]*$RM;
  $N_met2[$i] = $N_met2[$i-1]*(1-$MD-$D)+$N_met1[$i-1]*$MM;
  
  //Dead
  $N_dead[$i]= $N_dead[$i-1]+ ($N_healthy[$i-1]+$N_Tr[$i-1]+$N_Tu[$i-1]+$N_Te[$i-1]+$N_Tru[$i-1]+$N_Tre[$i-1]+$N_Teu[$i-1]+$N_True[$i-1]+$N_rec[$i-1])*$D+($N_met1[$i-1]+$N_met2[$i-1])*($MD+$D);
  
  //Calculate Utility
  $Baseline = 1.013-0.0038*($age+$i/12);
  $QALY = $QALY + 1/12*$Baseline*($N_healthy[$i]*$Hu+$N_rec[$i]*$Ru+($N_met1[$i]+$N_met2[$i])*$Mu + 
		  $N_Tr[$i]*$Tu_R + $N_Tu[$i]*$Tu_U + $N_Te[$i]*$Tu_E + 
		  $N_Tre[$i]*$Tu_RE + $N_Tru[$i]*$Tu_RU + $N_Teu[$i]*$Tu_UE + 
		  $N_True[$i]*$Tu_RUE)/((1+$DR_u)**($i/12));

}
$QALY_RT = $QALY;

//PROSTATECTOMY
  // Toxicity probabilities
if ($psa<10) $temp = 1; else $temp = 0;
$EF_S = 1/(1+exp(-(-2.96+0.45/10*$HRQOL+0.85*$temp-0.56*($age/10)+1.29*$Nerve))); $P1 = $EF_S; //erectile function
$P2a = 0;  //acute rectal bleeding
$R_S = 0; $P2 = 0; // late rectal bleeding
// Calculate the urinary incontinence points for age:
$age_points = -0.0000434476*$age**4 + 0.0082*$age**3 - 0.5143*$age**2 + 9.9092*$age + 80;
$UI_S = 1/(1+exp(-(-1.514+0.0664*$age_points-0.0202*$BMI-0.462*$ASA+0.13*$Ulength))); $P3 = $UI_S; //urinary incontinence
$total_points = 0.22*$tstage+0.88*$PGG+0.46*$SGG+0.08*$Pbiopsy-0.06*$Nbiopsy+0.43*log($psa,2);
$TCP_S = 1/(1+exp(-(3.16-0.076*$total_points**2-0.93*$total_points))); $P_Rec = (1-$TCP_S)+(1-$TCP_S)*0.05;//tumor control probability
// Define initial population numbers
$N_Tr[0] = $N_Tu[0] = $N_Tru[0] = $N_healthy[0] = $N_rec[0] = $N_met1[0] = $N_met2[0] = $N_dead[0] = 0;

// All patients start with erectile dysfunction, and a percentage start with urinary incontinence and rectal bleeding
$N_Te[0] = 0; 
$N_Tre[0] = 0;
$N_Teu[0] = 1;
$N_True[0] = 0;

$Baseline = 1.013-0.0038*$age;
$QALY = 1/12*$Baseline*($N_Te[0]*$Tu_E+$N_Tre[0]*$Tu_RE+$N_Teu[0]*$Tu_UE+$N_True[0]*$Tu_RUE);


$Costs = $RT_costs+$N_Te[0]*($Tc_E_a+$Hc_a12)+$N_Tre[0]*($Tc_R_a+$Tc_E_a+$Hc_a12)+$N_Teu[0]*($Tc_U_a+$Tc_E_a+$Hc_a12)+$N_True[0]*($Tc_R_a+$Tc_E_a+$Tc_U_a+$Hc_a12);

// start the markov chain
for ($i = 1; $i <= 240; $i++) {
  // calculate time dependant individual transition probabilities for this cycle
  if ($i <= 24) {$T1 = Calculate_transition($P1,24,$i);}else{$T1 = 0;};
  if ($i <= 12) {$T3 = Calculate_transition($P3,12,$i);}else{$T3 = 0;};
  
  // you cannot have all three toxicities with surgery, cause there is no rectal toxicity

  
  // you cannot have rectal toxicity and erectile dysfuntion because there is no rectal toxicity
  
  // Erectile dysfunction and Urinary incontinence 
  $T13H = $T1*$T3; // to healthy
  $T13T3 = $T1-$T13H; // to Urinary incontinence
  $T13T1 = $T3-$T13H; // to erectile dysfunction
  
  // Rectal bleeding and Urinary incontinence does not exist, because there is no rectal toxicity

  
  $T1H = $T1; // Erectile dysfunction to healthy
  $T3H = $T3; // Urinary incontinence to healthy
  
  // recurrence and metastatic disease
  $HR = (($P_Rec-$P_Rec*exp(-0.05*($i+1)))-($P_Rec-$P_Rec*exp(-0.05*($i))))/(1-($P_Rec-$P_Rec*exp(-0.05*($i+1))));
  //echo $HR; echo "<br>";
  $RM = ((0.84-0.84*exp(-0.05*($i+1)))-(0.84-0.84*exp(-0.05*$i)))/(1-(0.84-0.84*exp(-0.05*($i+1))));
  $MM = 1-exp(1/24*log(1-0.5));
  $MD = ((0.9-0.9*exp(-0.062*($i+1)))-(0.9-0.9*exp(-0.062*$i)))/(1-(0.9-0.9*exp(-0.062*($i+1))));
  
  // chance of non-cancer related death
  $P_D_year = 1.168e-05*exp( 0.1062*round($age+$i/12));
  if ($P_D_year >= 1){$P_D_year = 0.995;};
  $D  = 1-exp(1/12*log(1-$P_D_year));
  
  // calculate number of patients in each health state
  //Healthy
  $N_healthy[$i] = $N_healthy[$i-1]*(1-$HR-$D) + $N_Teu[$i-1]*$T13H +$N_Te[$i-1]*$T1H + $N_Tu[$i-1]*$T3H;
  
  //Toxicity
  $N_Te[$i] = $N_Te[$i-1]*(1-$T1H-$HR-$D) + $N_Teu[$i-1]*$T13T1;
  $N_Tr[$i] = 0;
  $N_Tu[$i] = $N_Tu[$i-1]*(1-$T3H-$HR-$D) + $N_Teu[$i-1]*$T13T3;
  
  
  $N_Tre[$i] = 0;
  $N_Tru[$i] = 0;
  $N_Teu[$i] = $N_Teu[$i-1]*(1-$T13T3-$T13T1-$T13H-$HR-$D);
  $N_True[$i] = 0;
  
  //Recurrence
  $N_rec[$i] = $N_rec[$i-1]*(1-$RM-$D)+$HR*($N_healthy[$i-1]+$N_Tu[$i-1]+$N_Te[$i-1]+$N_Teu[$i-1]);

  //Metastatic disease
  $N_met1[$i] = $N_met1[$i-1]*(1-$MM-$MD-$D)+$N_rec[$i-1]*$RM;
  $N_met2[$i] = $N_met2[$i-1]*(1-$MD-$D)+$N_met1[$i-1]*$MM;
  
  //Dead
  $N_dead[$i]= $N_dead[$i-1]+ ($N_healthy[$i-1]+$N_Tu[$i-1]+$N_Te[$i-1]+$N_Teu[$i-1]+$N_rec[$i-1])*$D+($N_met1[$i-1]+$N_met2[$i-1])*($MD+$D);
  
  //Calculate Utility
  $Baseline = 1.013-0.0038*($age+$i/12);
  $QALY = $QALY + 1/12*$Baseline*($N_healthy[$i]*$Hu+$N_rec[$i]*$Ru+($N_met1[$i]+$N_met2[$i])*$Mu + 
		  $N_Tr[$i]*$Tu_R + $N_Tu[$i]*$Tu_U + $N_Te[$i]*$Tu_E + 
		  $N_Tre[$i]*$Tu_RE + $N_Tru[$i]*$Tu_RU + $N_Teu[$i]*$Tu_UE + 
		  $N_True[$i]*$Tu_RUE)/((1+$DR_u)**($i/12));


}
$QALY_S = $QALY;
function Calculate_transition($P_total,$T_total,$Step){
	$Rate_total = -log(1-$P_total);
	$Rate = $Rate_total*(($Step-1)*-2/($T_total-1)+2)/$T_total;
	$P_transition = 1-exp(-$Rate);
	return $P_transition;
}
	
// Display results
echo "<table><tr><th>Outcome</th><th>Radiotherapy</th><th>Prostatectomy</th></tr>";
echo "<tr><td>Erectile dysfunction at 2 years</td><td>" .round((1-$EF_RT)*100). "%</td><td>" .round((1-$EF_S)*100). "%</td></tr>";
echo "<tr><td>Rectal bleeding at 3 years</td><td>" .round($R_RT*100). "%</td><td>0%</td></tr>";
echo "<tr><td>Urinary incontinence at 1 year</td><td>" .round($UI_RT*100). "%</td><td>" .round((1-$UI_S)*100). "%</td></tr>";
echo "<tr><td>Cancer free at 5 years</td><td>" .round($TCP_RT*100). "%</td><td>" .round($TCP_S*100). "%</td></tr>";
echo "<tr><td>QALYs after 20 years</td><td>" .round(10*$QALY_RT)/10 . "</td><td>" .round(10*$QALY_S)/10 . "</td></tr>";
echo "</table>"
?>

</body>
</html> 

    
