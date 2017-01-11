<?php

	error_reporting(E_ALL);

	require_once 'clearDir.php';

	//clearDir('Temps'); // Permet de purger le dossier temps et conservé les fichier temporaire si demandé pour un confort de consultation
	
	//header('Content-Type: text/html; charset=utf-8');
	header('Content-Type: text/event-stream; charset=utf-8');
	//header('Cache-Control: no-cache');

	/** CHARGEMENT DES FONCTIONS, CLASSES ET PARAMS **/
	require 'Template.class.php';

	/** DECLARATION DES VARIABLES **/
	/** DECLARATION DES VARIABLES POUR LE MOTEUR **/
	$vars = Array(
		"CREATE_PROCEDURE" => true,
		"CDN_1" => true,
		"CDN_2" => false,
		"CDN_3" => false
	);

	$vars_ud = Array(
		"CDN_2" => false,
		"CDN_3" => false,
		"CDN_4" => true
	);

	//$text = "<h1>Bienvenue %user%</h1>";

	/** 1. GENERATION DES DONNEES **/	

	/** 2. SORTI DES DONNEES **/
		/** 2.1. CREATION DU MOTEUR **/
		$moteur = new Template(false);
		
		//$moteur->set_render_type("Temporary");

		/** 2.2. AFFICHAGE DE L'AIDE **/
		//$moteur->help(); exit();

		/** 2.3. DEFINITION DU TEMPLATE **/
		//$moteur->set_template_text($text);

		//$moteur->set_template_file('Templates/dev-tpl.html');
		//$moteur->set_template_file('Templates/dev-set_var.tpl.html');
		//$moteur->set_template_file('Templates/dev-strip_crlf.tpl.json');
		//$moteur->set_template_file('Templates/dev-cleanse_js.tpl.json');
		//$moteur->set_template_file('Templates/dev-cleanse_sql.tpl.mysql');
		//$moteur->set_template_file('Templates/dev-block-with.tpl.html');
		//$moteur->set_template_file('Templates/dev-block-anonyme.tpl.html');
		//$moteur->set_template_file('Templates/dev-include-tpl.html');
		//$moteur->set_template_file('Templates/dev-block-tpl.html');
		//$moteur->set_template_file('Templates/dev-declare-and-use-tpl.html');
		//$moteur->set_template_file('Templates/dev-declare-template.tpl.html');

		//$moteur->set_template_file('Templates/dev-validation-tpl.html');
		//$moteur->set_template_file('Templates/dev-validation-2-tpl.html');

		/** 2.4. PENDING METHODE SELON BESOIN **/
		//$moteur->unset_template_text();
		//$moteur->unset_template_file();

		/** 2.5. MODE DE FONCTIONNEMENT **/
		//$moteur->set_vars(Array());
		//$moteur->set_utf8_write_treatment("DECODE");
		//$moteur->set_keep_temp_file(true);
		//$moteur->set_temporary_repository('Temps');
		//$moteur->set_render_type('permanent');
		$moteur->set_vars($vars);
		//$moteur->unset_vars("String", Array());
		$moteur->get_vars();
		//$moteur->unset_vars('CDN_1');
		//$moteur->update_vars($var_ud);
		$moteur->xor_vars('CDN_1', 'CDN_2');
		$moteur->get_vars();
		//$moteur->unset_vars('CDN_2', 'CDN_3');
		//$moteur->unset_vars(Array('CDN-1', 'CDN_2'));
		//$moteur->set_vars_delim('A{%(%[Z');
		//$moteur->get_vars();
		//$moteur->set_vars_delim('{');

		/** 2.6. GENERATION NUMERO 1 **/
		//$moteur->set_output_directories('Repo1', 'Repo2');
		//$moteur->set_template_source('Templates/dev-render-code.tpl.html');
		//$moteur->set_output_name('resultat.html');
		//$moteur->render();
		//$t = $moteur->get_render_content();
		//$t = Template::strip_blank($t);
		//$t = Template::strip_blank($moteur->get_render_content());
		//$moteur->render()->display();
		//echo Template::cleanse_sql($moteur->render()->get_render_content(), false);
		//echo Template::cleanse_sql(file_get_contents('Templates/dev-cleanse_sql.tpl.mysql'), false);
		//$moteur->cleansing_render_env();
		//$moteur->display();

		//$moteur->add_history("MESSAGE 1", E_USER_WARNING);
		//$moteur->add_history("MESSAGE 2", E_USER_WARNING);
		//$moteur->add_history("MESSAGE 3", E_USER_WARNING);
		//$moteur->add_history("MESSAGE 4", E_USER_WARNING);
		//$moteur->add_history("MESSAGE 5", E_USER_WARNING);

		//$moteur->show_historic();
		//$moteur->show_historic(true);
		//$moteur->show_historic(false);

		/** 2.7. GENERATION NUMERO 2**/
		//$moteur->set_temporary_repository('Temps2');
		//$moteur->set_output_directories('Repo2');
		//$moteur->set_template_file('Templates/dev-file-n-text---text-tpl.html');
		//$text = file_get_contents('Templates/dev-file-n-text---text-tpl.html');
		//$moteur->set_template_text($text);
		//$moteur->set_output_name('resultat2.html');
		//$moteur->render();
		//$moteur->display();
		//$moteur->show_warnings();
		//$moteur->get_vars();
		//$moteur->set_var('CDN_1', false);
		//$moteur->get_vars();
		//$moteur->set_var('CDN_2', true);
		//$moteur->get_vars();
		//$moteur->render()->display();

		// Validation numéro 1
		//include('data-validation.php');
		//$moteur->set_vars($vars);
		//$moteur->set_vars_delim('{');
		//$moteur->render()->display();

		// Validation numéro 2
		//include('data-validation2.php');
		//$moteur->set_vars($vars);
		//$moteur->render()->display();

		/** NETTOYAGE TEMP SI BESOIN **/
		//$moteur->remove_folder('Temps');
		//mkdir('Temps');
?>