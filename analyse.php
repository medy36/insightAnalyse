<?php

/*
* The import Class of redmine reporting application.
*
* (c) Lasri Mehdi
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/



/**		
 * This class contains methodes to generate a Reporting from Insight of a given Commit ID
 *
 *
 * @author Lasri Mehdi <lasri.mehdi@gmail.com>
 */
CLass gitCommit
{


	function __construct($idCommit)
	{
		if(!isset($idCommit))
		{
			$help="
		* =====================================
		*  Help Section:\n
		*			
		*
		*  ID commit is missing, run cmd as: \n
		*   php result.php 108f796dc0fbfbf4f50227fe110baf317d (replace the Id by the commit you want to check) \n
		* =====================================\n
		*/";
			echo $help;
		} 
 	}

 	/**
	 *  get Id of last git commit
	 *  not used anymore, since idCommit is a parameter in CLI
	 *
	 * @return $idCommit
	 */
	public function getIdCommit()
	{
		// charge the shell script git log to json
		$contents = file_get_contents('git-log2json.sh');
		$var = shell_exec($contents);
		$varEncode= json_decode($var, true);
		$idCommit= $varEncode[0]["commit"];

		return $idCommit;

	}
	/**
	 *  get analyse from Insight in format Json 
	 *
	 * @return $insightJson
	 */
	public function loadInsightXml()
	{
		//TODO: This can be dynamic 
		// get violations of the project UUID given - 0fca7278-a2ed-410e-9a5b-f8fefd993264

		$getLastAnalyseViolations = "sudo php insight.phar analysis 0fca7278-a2ed-410e-9a5b-f8fefd993264  --format=\"json\" ";

		$jsonResultViolations = shell_exec($getLastAnalyseViolations);
		$insightJson = json_decode($jsonResultViolations, true);

		return $insightJson;

	}

	/**
	 *  get path of files fired in Commit
	 *
	 * @return $commitedFiles
	 */
	public function gitListFilesChanged()
	{
		Global $argv;
	
		//check if paramter is given in CLI
		if(isset($argv[1]))
		{
			
			$idCommit = $argv[1];
			
		}
		else{

			echo "
		* =====================================
		*    ID commit is missing!!!
		* 		 Help Section:\n
		*			
		* By default Insight send the last commit informations, if you want to change it add the commit id like:
		*  \n
		*   php result.php 108f796dc0fbfbf4f50227fe110baf317d (replace the Id by the commit you want to check) \n
		* =====================================\n
		*/
		\n";
		}
		$commitedFiles= array();
		//get insight commits
	    $contents = file_get_contents('git-log2json.sh');
	    //exec the shell script
		$var = shell_exec($contents);
		// decode result 
	    $varEncode= json_decode($var, true);
	    //get last commit ID
	    //add Commit by ID
		// $idCommit=  $varEncode[0]["commit"];
		// var_dump($idCommit); die;

		//get commited files by user
		if(!$idCommit)
		{
			echo "Veuillez ajouter l'ID de votre Commit\n";
		}
		$list =shell_exec("git show --pretty=\"format:\" --name-only ".$idCommit);
		// Eclatememnt du resultat
		$r = preg_split("/[\s,]+/", $list);
		// set commitedFiles
		foreach ($r as $key => $value) {
			if($value)
			{
				array_push($commitedFiles, $value);
			}
		}

		return $commitedFiles;

	}


	public function main()
	{

		$listFilesChanged = $this->gitListFilesChanged();
		$jsonDecodeVilations = $this->loadInsightXml();
		$parsJson = $jsonDecodeVilations["violations"];
		$violResource = array();
		$violSeverity = array();
		$violDescription = array();
		$violLine = array();
		$viols = 0;


		if (isset($jsonDecodeVilations["violations"])) {
			foreach ($jsonDecodeVilations["violations"] as $allViolations) {
				foreach ($allViolations as $violation) {
					// var_dump($violation['resource']);
					if(isset($violation['resource']))
					{
		  			if(in_array($violation['resource'],$listFilesChanged))
		  			{
		  				$viols ++;
		  				array_push($violResource, $violation["resource"]);
		  				array_push($violSeverity, $violation["severity"]);
		  				array_push($violDescription, $violation["message"]);
		  				array_push($violLine, $violation["line"]);

		  			}
		  		}
				}
			
			}
		}
		$md_array["severity"][] = "";
		$md_array["resource"][] = "";
		$md_array["description"][] = "";

		for ($i=0; $i < count($violResource); $i++) { 

			array_push($md_array['severity'],$violSeverity[$i]);
			array_push($md_array['resource'],$violResource[$i]);
			array_push($md_array['description'],$violDescription[$i]);
		}

		$info = 0;
		$major =0;
		$minor =0;
		$critical=0;
		$severty = array();

        if($violSeverity)
        {

	        foreach ($violSeverity as $key => $value) 
	        {
	            switch ($value)
	             {
	                case 'info':
	                   $info++;

	                    break;
	                 case 'minor':
	                   $minor++;

	                    break;
	                 case 'major':
	                   $major++;

	                    break;
	                case 'critical':
	                   $critical++;

	                    break;
	            }
	             
	         }
           }else
           {
           	  // INIT ALL vars to Zero
              $info =0;
              $minor=0;
              $major=0;
              $critical=0;
           }

			$header="* =====================================
			* Statistique des erreurs sur Insight: \n
			*	Critical: ".$critical."  \n
			*	Major: ".$major."   \n
			*	Minor:  ".$minor." \n
			*	Info:   ".$info." \n
			";
			
			
			          echo $header;
			$body="\n* =====================================
			\n  #  Severity:         Resource:               					 Message:           \n";

			echo $body;

		for ($col=1; $col < count($md_array["severity"]); $col++)
		{ 
			echo "=>  ".$md_array["severity"][$col]."       ".$md_array["resource"][$col]."      \n";#" ".$md_array["description"][$col]| ."\n";
		}

		
     	echo " * =====================================* \n";


	}



} 


  
$analyse = new gitCommit($argv[1]);
$analyse->main();
