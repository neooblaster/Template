<?php
/**
 * Fichier :: Template.php
 *
 * %DESC BLOCK%
 *
 * @author    Nicolas DUPRE
 * @release   24/08/2017
 * @version   3.6.0
 * @package   Template
 *
 * @TODO : Permettre d'assigner un ou plusieurs jeu de donnée à un block
 * @TODO : Refaire Doc
 * @TODO : Template->get_if_block_name() failed; Keyword 'AS' is missing in this conditionnal instruction
 * @TODO : Erreur open_render_file truc, probleme de droit sur le dossier, voir si constant pour user web et l'indiqué
 * @TODO : Automatiser output_name (facultative)
 * @TODO : Auto aliasé les block IF si AS not used
 * @TODO : Permettre d'assigner un ou plusieurs jeu de donnée à un block
 *
 */

namespace Template;

/**
 * @var Array _PHP Variable Globale permettant l'interface entre le PHP des Templates et les scripts appelants.
 */
$GLOBALS["_PHP"] = Array();

/**
 * Class Template
 * @package Template
 */
class Template {
    /**
     * Déclaration des extensions des fichiers selon leur type fonctionnel
     */
	const BLOCK_EXT = '.block';
	const IF_BLOCK_EXT = ".if";
	const MASTER_EXT = ".master";
	const PHP_BLOCK_EXT = ".php";
	const TEMPLATE_EXT = ".itpl";

    /**
     * @var string $_asbolute_path Chemin absolu vers la classe pour les inclusions de donnée de la classe
     */
	protected $_asbolute_path;

    /**
     * @var array $_buffered_names Liste des blocks enregistrés dont la valeur correspond au nom de fichier temporaire
     */
	protected $_buffered_names = Array();

    /**
     * @var array $_buffered_files_res Liste de ressources correspondant au fichier temporaire ouvert pour écriture (buffering)
     */
	protected $_buffered_files_res = Array();

    /**
     * @var array $_buffered_flow_records Liste de Nom et Type de block en cours de bufferisation au niveau X donnée
     */
	protected $_buffered_flow_records = Array();

    /**
     * @var int $_buffered_flow_level Niveau de bufferisation en cours
     */
	protected $_buffered_flow_level = -1;

    /**
     * @var bool $_debug_mode Indique si l'execution de la classe est en mode debug - Qui affiche tout les erreurs non bloquante
     */
	protected $_debug_mode;

    /**
     * @var int $_use_vars_level Niveau d'utilisation de variable (pour les imbrications de block)
     */
	protected $_use_vars_level = -1;

    /**
     * @var array $_use_vars_ref Référence des variables correspondant au niveau d'utilisation de variable
     */
	protected $_use_vars_ref = Array();

    /**
     * @var bool $_keep_temp_file Indicateur qui indique si oui ou non on concerve les fichiers temporaire quelque soit le mode de rendu.
     */
	protected $_keep_temp_file = false;

    /**
     * @var string $_mail_recipients Adresses mail des destinataire de la methode "sendMail"
     */
	protected $_mail_recipients;

    /**
     * @var string $_mail_subject Object du mail pour la methode "sendMail"
     */
	protected $_mail_subject;

    /**
     * @var string $_mail_sender Adresse mail de l'envoyeur du mail pour la methode "sendMail"
     */
	protected $_mail_sender;

    /**
     * @var string $_mail_sender_name Nom de l'envoyeur du mail pour la methode "sendMail"
     */
	protected $_mail_sender_name;

    /**
     * @var array $_output_directories Dossiers de sortie pour le rendu (faculatif)
     */
	protected $_output_directories;

    /**
     * @var resource $_output_file_res Mise en cache du fichier de sortie pour écrire par différente methodes
     */
	protected $_output_file_res;

    /**
     * @var string $_output_name Nom du fichier de sortie avec l'extension (obligatoire)
     */
	protected $_output_name;

    /**
     * @var bool $_remove_folder_secure Flag anti-boucle infinie en cas de blocage de suppression de fichier pour remove_folder
     */
	protected $_remove_folder_secure;

    /**
     * @var int $_render_depth_level Niveau de profondeur d'execution de la methode render (en cas d'imbrication de template, ertaine action ne doivent pas etre executée )
     */
	protected $_render_depth_level;

    /**
     * @var bool $_render_env_exist Flag qui indique que l'environnement de rendu exist. EN cas d'imbrication de template, on ne recréer pas l'env
     */
	protected $_render_env_exist = false;

    /**
     * @var string $_render_type Flag qui indique le type de rendu attendu (permanent | temporary )
     */
	protected $_render_type;

    /**
     * @var bool $_has_rendered Flag qui indique si la classe à procédé à un rendu {false} - utile pour get_render_content dans le cas render type "permanent"
     */
	protected $_has_rendered;

    /**
     * @var array $_templates_files_res Mise en cache du contenu des fichiers templates pour lecture multiple selon les besoin
     */
	protected $_templates_files_res = Array();

    /**
     * @var string $_template_file Chemin complet avec le nom du template (extension comprise) (obligatoire)
     */
	protected $_template_file = null;

    /**
     * @var bool $_template_file_used Indique que la source est un fichier de modèle
     */
	protected $_template_file_used = false;

    /**
     * @var string $_template_text Text à assimiler comme un template
     */
	protected $_template_text = null;

    /**
     * @var bool $_template_text_used Indique que la source est un texte
     */
	protected $_template_text_used = false;

    /**
     * @var string $_temporary_directory Nom du dossier pour la création de dossier temporaire, unique a la session executant la classe
     */
	protected $_temporary_directory;

    /**
     * @var bool $_temporary_file_openned Indique si le fichier temporaire dans lequel on génère le rendu est ouvert
     */
	protected $_temporary_file_openned = false;

    /**
     * @var string $_temporary_render_file chemin approprié vers le fichier rendu temporaire
     */
	protected $_temporary_render_file;

    /**
     * @var resource $_temporary_render_file_res Fichier en cache de rendu de travail temporaire
     */
	protected $_temporary_render_file_res;

    /**
     * @var string $_temporary_repository Chemin vers le dossier hebergeant les dossiers temporaires avec les fichiers
     */
	protected $_temporary_repository = null;

    /**
     * @var string $_temporary_folders_path Chemin complet de dépot des fichiers temporaire (combinaison du chemin vers le dossiers d'accueil et le dossier temporaire de session
     */
	protected $_temporary_folders_path;

    /**
     * @var string $_utf8_write_treatment Spécifie le méthode de traitement UTF8 d'écriture du rendu(encode, decode, none), default: none
     */
	protected $_utf8_write_treatment;

    /**
     * @var string $_utf8_read_treatment Spécifie le méthode de traitement UTF8 de lecture du rendu(encode, decode, none), default: none
     */
	protected $_utf8_read_treatment;

    /**
     * @var array $_vars Ensemble des données servant a la renderisation
     */
	protected $_vars;

    /**
     * @var array $_historic Stockage de tout les erreurs rencontrées
     */
	protected $_historic;

    /**
     * @var array $_PHP Lien entre la superglobale $_PHP et la classe php Template
     */
	protected $_PHP;
    /**
     * @var string $ Délimiteur d'ouverture de variable évalué pour les patterns
     */
	protected $_start_var_delim;

    /**
     * @var string $_end_var_delim Délimiteur de fermeture de variable évalué pour les patterns
     */
	protected $_end_var_delim;

    /**
     * @var string $_start_var_delim_def Délimiteur d'ouverture de variable défini
     */
	protected $_start_var_delim_def;

    /**
     * @var string $_end_var_delim_def Délimiteur de fermeture de variable défini
     */
	protected $_end_var_delim_def;

	/**
     * @var string $_ins_open_tag Balise ouvrante des instructions du moteur Template (Pattern pour RegExp)
     */
	protected $_ins_open_tag;

    /**
     * @var string $_ins_close_tag Balise Fermante des instructions du moteur Template (Pattern pour RegExp)
     */
	protected $_ins_close_tag;

    /**
     * @var string $_ins_open_tag_def Balise ouvrante des instruction du moteur Template (text pure)
     */
	protected $_ins_open_tag_def;

    /**
     * @var string $_ins_close_tag_def Balise ouvrante des instruction du moteur Template (text pure)
     */
	protected $_ins_close_tag_def;

	
/** -------------------------------------------------------------------------------------------------------------------- 
/** --------------------------------------------------------------------------------------------------------------------
/** ---																																					---
/** --- 														{ C O N S T R U C T E U R S } 													---
/** ---																																					---
/** --------------------------------------------------------------------------------------------------------------------
/** -------------------------------------------------------------------------------------------------------------------- **/
	/** --------------------------------------------- **
	/** --- Execution à la création de l'instance --- **
	/** --------------------------------------------- **/
	function __construct($debug_mode=false){
		/** Controle de l'argument **/
		if(!is_bool($debug_mode)){
			/** Envois forcé pour le constructeur - Niveau WARNING **/
			$this->throw_error(sprintf('Template::__construct() expects parameter 1 to be boolean, %s given in.', gettype($debug_mode)), E_USER_WARNING);
		}
		
		/** Initialisation des paramètres **/
		$this->_absolute_path = dirname(__FILE__);
		$this->_blocks_vars = Array();
		$this->_output_directories = Array('.');
		$this->_utf8_write_treatment = 'none';
		$this->_utf8_read_treatment = 'none';
		$this->_remove_temporary_directory_secure = 1;
		$this->_remove_folder_secure = 1;
		$this->_render_depth_level = -1;
		$this->_render_type = 'temporary';
		$this->_start_var_delim = '%';
		$this->_end_var_delim = '%';
		$this->_vars = Array();
		$this->_has_rendered = false;
		$this->_historic = Array();
		$this->_debug_mode = $debug_mode;
			
		/** GENERATION D'UN NOM DE DOSSIER TEMPORAIRE UNIQUE (CHANCE DE DOUBLON CASIEMENT NULLE)**/
		if(session_id() !== ''){
			$this->_temporary_directory = session_id();
		} else {
			$tirage = rand(0, 9999999);
			$this->_temporary_directory = sha1($tirage.time());
		}
		
		/** DECLENCHEMENT DE METHODE **/
		$this->update_temporary_folders_path();
		$this->set_template_tags();
		
		return true;
	}
	
	/** -------------------------------------------------------------- **
	/** --- Execution à la destruction (invoqueé ou fin de script) --- **
	/** -------------------------------------------------------------- **/
	function __destruct(){
		$this->cleansing_render_env(true);
		$this->cleansing_render_env_root();
	}

    /**
     * Identifie le nom du block de l'instruction soumise
     * @param string $instruction
     * @return mixed
     */
	private function get_if_block_name($instruction){
		/** Vérifier la présence du mot clé AS **/
		$as_pattern = "#\s+((a|A){1}(s|S){1})\s+#";
		
		if(preg_match($as_pattern, $instruction)){
			$matches = Array();
			preg_match('#((AS)|(As)|(aS)|(as))\s+[a-zA-Z0-9-_.]+\s+#', $instruction, $matches);
			
			/** REMOVE AS AND SPACES **/
			$if_name = preg_replace('#((AS)|(As)|(aS)|(as))\s+#', '', $matches[0]);
			$if_name = preg_replace('#\s+$#', '', $if_name);
			
			return $if_name;
		} else {
			$this->throw_error(sprintf("Template::get_if_block_name() failed; Keyword 'AS' is missing in this conditionnal instruction : %s", $instruction), E_USER_ERROR);
			return false;
		}
	}

    /**
     * Renvoie les déstinataires pour l'envoie de mail par la methode "sendMail"
     * @return string
     */
	public function get_mail_recipients(){
		return $this->_mail_recipients;
	}

    /**
     * Renvoie la valeur défini pour l'originaire du mail de la méthode "sendMail"
     * @return string
     */
	public function get_mail_sender(){
		return $this->_mail_sender;
	}

    /**
     * Renvoie la valeur du nom de l'originaire du mail par la methode "sendMail"
     * @return string
     */
	public function get_mail_sender_name(){
		return $this->_mail_sender_name;
	}

    /**
     * Renvoie la valeur définie pour l'object du mail pour la méthode "sendMail"
     * @return string
     */
	public function get_mail_subject(){
		return $this->_mail_subject;
	}

    /**
     * Renvoie la valeur définie pour le dossier de sortie pour le rendu
     * @return array
     */
	public function get_output_directories(){
		return $this->_output_directories;
	}

    /**
     * Renvoie la valeur définie pour le nom de fichier de sortie lors du rendu
     * @return string
     */
	public function get_output_name(){
		return $this->_output_name;
	}

    /**
     * Retourne le contenu du fichier généreé
     * @return bool|string Boolean on failed; String on success
     */
	public function get_render_content(){
		$render_content = false;
		
		/** Selon le mode de rendu, on affiche le fichier temporaire, soit on affiche de document final, déposé **/
		if($this->_render_type == 'temporary'){
			/** S'assurer que tout s'est bien passé **/
			if(file_exists($this->_temporary_folders_path.'/renders/'.$this->_output_name)){
				/** Affichage du fichier selon le traitement demandé **/
				switch($this->_utf8_read_treatment){
					case 'none':
						$render_content = file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name);
					break;
					case 'encode':
						$render_content = utf8_encode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
					break;
					case 'decode':
						$render_content = utf8_decode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
					break;
				}
			} else {
				$this->throw_error('Template->get_render_content() with render_type="temporary" failed. The render has not been done or the file not exist. Use Template->render();', E_USER_ERROR);
			}
		} 
		/** Mode permanent **/
		else {
			if(file_exists($this->_output_directories[0].'/'.$this->_output_name)){
				/** Affichage du fichier selon le traitement demandé **/
				switch($this->_utf8_read_treatment){
					case 'none':
						$render_content = file_get_contents($this->_output_directories[0].'/'.$this->_output_name);
					break;
					case 'encode':
						$render_content = utf8_encode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
					break;
					case 'decode':
						$render_content = utf8_decode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
					break;
				}
				
				if(!$this->_has_rendered){
					$this->add_history('Template->get_render_content() has retrieves the data of an existing document whereas there is no render which has been done.', E_USER_WARNING);
				}
			} else {
				$this->throw_error('Template->get_render_content() with render_type="permanent" failed. The render has not been done or the file not exist. Use Template->render();', E_USER_ERROR);
			}
		}
		
		return $render_content;
	}

    /**
     * Renvois la valeur saisie dans la zone dédiée de l'instruction HTML
     * @param string $instruction
     * @return bool|string
     */
	private function get_input_param($instruction){
		/** Traitement de l'instruction pour obtenir le template et son path **/
		$start_index = strpos($instruction, '(');
		$end_index = strrpos($instruction, ')');
		$input_param = substr($instruction, ($start_index + 1), ($end_index - $start_index - 1));
		
		return $input_param;
	}

    /**
     * Renvoie la valeur définie pour le template à utiliser
     * @return string
     */
	public function get_template_file(){
		return $this->_template_file;
	}

    /**
     * Renvois le nom du fichier du modèle demandé
     * @param $template_file
     * @return mixed
     */
	public function get_template_file_name($template_file){
		return $template_file;
	}

    /**
     * Renvoie le text défini comme modèle
     * @return string
     */
	public function get_template_text(){
		return $this->_template_text;
	}

    /**
     * Affiche une vue des variables simple
     * @return mixed
     */
	public function get_vars(){
		return print_r($this->_vars);
	}

    /**
     * Affiche une vue du délimiteurs de variables
     * @return array
     */
	public function get_vars_delim(){
		return Array('Start Delimiter' => $this->_start_var_delim_def, 'End Delimiter' => $this->_end_var_delim_def);
	}

    /**
     * Défini le mode de debbuguage
     * @param $debug_mode
     * @return bool
     */
	public function set_debug_mode($debug_mode){
		if(is_bool($debug_mode)){
			$this->_debug_mode = $debug_mode;
			return true;
		} else {
			$this->add_history(sprintf('Template::debug_mode() expects parameter 1 to be boolean, %s given in.', gettype($debug_mode)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Sert à définir si l'on concerve les fichiers temporaire du rendu
     * @param $keep_file
     * @return bool
     */
	public function set_keep_temp_file($keep_file){
		if(!is_bool($keep_file)){
			$this->add_history(sprintf('Template::set_keep_temp_file() expects parameter 1 to be boolean, %s given in.', gettype($keep_file)), E_USER_WARNING);
		}
		$this->_keep_temp_file = $keep_file;
		
		return true;
	}

    /**
     * Défini le ou les destinataires pour l'envois de mail
     * @param null|string $recipient Autant d'argument string étant des adresses mail.
     * @return bool
     */
	public function set_mail_recipients($recipient=null){
		/** Initialisation des variables locales **/
		$recipients = Array();
		$output = '';
		
		/** Sécurisation des arguments **/
		if(!is_null($recipient)){
			foreach(func_get_args() as $arg){
				if(is_string($arg)){
					$recipients[] = $arg;
				} else {
					$this->add_history(sprintf('Template::set_mail_recipients() expects parameter to be String, "%s" given in.', gettype($arg)), E_USER_WARNING);
					if($this->_debug_mode) $this->throw_error(sprintf('Template::set_mail_recipients() expects parameter to be String, "%s" given in.', gettype($arg)), E_USER_WARNING);
				}
			}
		} else {
			$this->add_history('Template::set_mail_recipients() failed; At least 1 argument must be given.', E_USER_WARNING);
			if($this->_debug_mode) $this->throw_error('Template::set_mail_recipients() failed; At least 1 argument must be given.', E_USER_WARNING);
			return false;
		}
		
		/** Traitements **/
		for($i = 0; $i < count($recipients); $i++){
			$output .= ($output == null) ? $recipients[$i] : '; '.$recipients[$i];
		}
		
		/** Finalisation **/
		$this->_mail_recipients = $output;
		return true;
	}

    /**
     * Défini l'adresse mail de l'émetteur du mail
     * @param $sender
     * @return bool
     */
	public function set_mail_sender($sender){
		if(is_string($sender)){
			$this->_mail_sender = $sender;
			return true;
		} else {
			$this->add_history(sprintf('Template::set_mail_sender() expects parameter 1 to be String, %s given in.', gettype($sender)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini le nom d'affichage de l'émetteur du mail
     * @param $sender_name
     * @return bool
     */
	public function set_mail_sender_name($sender_name){
		if(is_string($sender_name)){
			$this->_mail_sender_name = $sender_name;
			return true;
		} else {
			$this->add_history(sprintf('Template::set_mail_sender_name() expects parameter 1 to be String, %s given in.', gettype($sender_name)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini l'objet du mail qui sera envoyé
     * @param $subject
     * @return bool
     */
	public function set_mail_subject($subject){
		if(is_string($subject)){
			$this->_mail_subject = $subject;
			return true;
		} else {
			$this->add_history(sprintf('Template::set_mail_subject() expects parameter 1 to be String, %s given in.', gettype($subject)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Définie le ou les dossiers de dépôts du document généré
     * @param string $directory n argument string définissant un dossier de dépôt
     * @return bool
     */
	public function set_output_directories($directory){
		/** Suppression eventuelle de l'env de rendu **/
		if($this->_render_env_exist){
			$this->cleansing_render_env(true);
		}
		
		/** Mise a jour des paramètres **/
		$this->_output_directories = func_get_args();
		$this->update_temporary_folders_path();
		
		/** Si on fonctionne à partir d'un modèle de text, l'environnement ayant été purgé, le recreer **/
		if($this->_template_text_used){
			$this->set_text_as_file();
		}
		
		return true;
	}

    /**
     * Défini le nom du fichier qui sera généré et déposé (si mode permanent)
     * @param string $name
     * @return bool
     */
	public function set_output_name($name){
		/** Mise à jour des données **/
		if(is_string($name)){
			$this->_output_name = $name;
			return true;
		} else {
			$this->add_history(sprintf('Template::set_output_mail() expects parameter 1 to be String, %s given in.', gettype($name)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini le type de rendu du document (Temporaire / Permanent)
     * @param string $type temporary, permanent
     * @return bool
     */
	public function set_render_type($type){
		$type = strtolower($type);
		
		if($type === 'temporary' OR $type === 'permanent'){	
			$this->_render_type = $type;
			return true;
		} else {
			$this->add_history('Template::set_render_type() failed. A wrong value has been sent. The accepted values are "temporary" or "permanent".', E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini le modèle source à utiliser
     * @param $source
     * @return bool
     */
	public function set_template_file($source){
		/** Vérifie si l'utilisateur n'a pas déjà opté pour le mode "TEXT" **/
		if(!$this->_template_text_used){
			$this->unset_template_text();
		}
		
		$this->_template_file = $source;
		$this->_template_file_used = true;
		
		return true;
	}

    /**
     * Défini les séquence d'ouverture et fermeture d'une instruction du moteur.
     * @param null|string $open_tag Modèle d'ouverture
     * @param null|string $close_tag Modèle de fermeture
     */
	public function set_template_tags($open_tag=null, $close_tag=null){
		/** > Sécurisation des arguments **/
		//--- Tage de fermeture
		if(is_null($open_tag) && is_null($close_tag)){
			$close_tag = "-->";
		} else if(!is_string($close_tag)) {
			$close_tag = "";
		}
		
		//--- Tag d'ouverture
		if(is_null($open_tag) || !is_string($open_tag)) $open_tag = "<!--";
		
		
		/** > Echappement des caractères **/
		$this->_ins_open_tag = addcslashes($open_tag, "/");
		$this->_ins_close_tag = addcslashes($close_tag, "/");
		
		/** > Sauvegarde du modèle défini **/
		$this->_ins_open_tag_def = $open_tag;
		$this->_ins_close_tag_def = $close_tag;
	}

    /**
     * Défini le text fournis comme modèle source
     * @param $text
     * @return bool
     */
	public function set_template_text($text){
		/** Si un modele au format fichier est déjà défini **/
		if(!$this->_template_file_used){
			$this->unset_template_file();
		}
		
		/** Enregistrer le text **/
		$this->_template_text = $text;
		$this->_template_text_used = true;
			
		/** L'environnement de travail doit exister **/
		if(!$this->_render_env_exist){
			$this->make_render_env();
		}
			
		/** Créer un fichier temporaire en guise de fichier de modèle pour concerver le mode de fonctionnement des autre méthodes **/
		$this->set_text_as_file();
		
		return true;
	}

    /**
     * Défini le dossier de dépôt des fichiers temporaire
     * @param $tmp_repository
     * @return bool
     */
	public function set_temporary_repository($tmp_repository){
		/** Suppression eventuelle de l'env de rendu **/
		if($this->_render_env_exist){
			$this->cleansing_render_env(true);
		}
		
		/** Mise à jour des paramètres **/
		$this->_temporary_repository = $tmp_repository;
		$this->update_temporary_folders_path();
		
		/** Si on fonctionne à partir d'un modèle de text, l'environnement ayant été purgé, le recreer **/
		if($this->_template_text_used){
			$this->set_text_as_file();
		}
		
		return true;
	}

    /**
     * Sauvegarde le text qui doit servir de modèle sous forme de fichier
     * @return bool
     */
	private function set_text_as_file(){
		/** Si l'environnement de rendu n'existe pas, le créer (peut avoir été supprimer suite à un changement de repository (temp/depot)) **/
		if(!$this->_render_env_exist){
			$this->make_render_env();
		}
		
		/** Créer le fichier et/ou le purger **/
		$text_template = fopen($this->_temporary_folders_path.'/temps/text_template.tpl', "w+");
		fclose($text_template);
		
		/** Injecter le texte **/
		file_put_contents($this->_temporary_folders_path.'/temps/text_template.tpl', $this->_template_text);
		
		/** Assimiler en tant que template source **/
		$this->_template_file = $this->_temporary_folders_path.'/temps/text_template.tpl';
		
		return true;
	}

    /**
     * Défini le traitement UTF-8 qui sera appliqué lors de la lecture des fichiers généré
     * @param string $treatment Traitement UTF-8 : none, encode, decode
     * @return bool
     */
	public function set_utf8_read_treatment($treatment){
		/** Initialisation des variables locales **/
		$accept_values = Array('none', 'encode', 'decode');
		
		/** Controle des argument **/
		if(is_string($treatment)){
			if(in_array(strtolower($treatment), $accept_values)){
				$this->_utf8_read_treatment = strtolower($treatment);
				return true;
			} else {
				$this->add_history(sprintf('Template::set_utf8_read_treatment() expects value "none", "encode" or "decode", %s given in.', $treatment), E_USER_WARNING);
				return false;
			}
		} else {
			$this->add_history(sprintf('Template::set_utf8_read_treatment() expects parameter 1 to be String, %s given in.', gettype($treatment)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini le traitement UTF-8 qui sera appliqué lors de la génération du document
     * @param string $treatment Traitement UTF-8 : none, encode, decode
     * @return bool
     */
	public function set_utf8_write_treatment($treatment){
		/** Initialisation des variables locales **/
		$accept_values = Array('none', 'encode', 'decode');
		
		/** Controle des argument **/
		if(is_string($treatment)){
			if(in_array(strtolower($treatment), $accept_values)){
				$this->_utf8_write_treatment = strtolower($treatment);
				return true;
			} else {
				$this->add_history(sprintf('Template::set_utf8_write_treatment() expects value "none", "encode" or "decode", %s given in.', $treatment), E_USER_WARNING);
				return false;
			}
		} else {
			$this->add_history(sprintf('Template::set_utf8_write_treatment() expects parameter 1 to be String, %s given in.', gettype($treatment)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini ou met à jour une variable
     * @param string $var_name
     * @param mixed $var_value
     * @return bool
     */
	public function set_var($var_name, $var_value){
		$this->_vars[$var_name] = $var_value;
		return true;
	}

    /**
     * Défini le jeu de donnée à utiliser - Ecrase l'existant
     * @param array $vars
     * @return bool
     */
	public function set_vars($vars){
		if(is_array($vars)){
			$this->_vars = $vars;
			return true;
		} else {
			$this->add_history(sprintf('Template::set_vars() expects parameter 1 to be Array, %s given in.', gettype($vars)), E_USER_WARNING);
			return false;
		}
	}

    /**
     * Défini le modèle définissant une variable
     * @param $delim
     * @return bool
     */
	public function set_vars_delim($delim){
		/** Définition des délimiteurs ayant un caractère inverse de fermeture **/
		$has_end_char = Array(
			"{" => "}",
			"(" => ")",
			"[" => "]"
		);
		
		/** Caractères à échapper **/
		//$to_escape = Array("{","}","(",")","[","]");
		
		/** Analyser le délimiteur **/
		$start_delim_def = $delim;
		$end_delim_def = null;
		$start_delim = null;
		$end_delim = null;
		
		$delim_length = strlen($delim) - 1;
		
		/** Parcourir l'ensemble des caractères en partant de la fin >>> Composition du delim de fin **/
		for($i = $delim_length; $i >= 0; $i--){
			/** Si le caractère analysé dispose d'un caractère de fin, alors on assimile le caractère de fermeture **/
			if(array_key_exists($delim[$i], $has_end_char)){
				$end_delim_def .= $has_end_char[$delim[$i]];
				$end_delim .= "[".$has_end_char[$delim[$i]]."]{1}";
			}
			/** Sinon c'est le même **/
			else {
				$end_delim_def .= $delim[$i];
				$end_delim .= "[".$delim[$i]."]{1}";
			}
		}
		
		/** Composition du délim de début **/
		for($i = 0; $i <= $delim_length; $i++){
			$start_delim .= "[".$delim[$i]."]{1}";
		}
		
		/** Sauvegarder des délimiteurs défini **/
		$this->_start_var_delim_def = $start_delim_def;
		$this->_end_var_delim_def = $end_delim_def;
		$this->_start_var_delim = $start_delim;
		$this->_end_var_delim = $end_delim;
		
		return true;
	}

    /**
     * Désaloue le flag indiquant l'utilisation d'un modèle provenant d'un fichier
     * @return bool
     */
	public function unset_template_file(){
		$this->_template_file = null;
		$this->_template_file_used = false;
		
		return true;
	}

    /**
     * Désalou le flag indiquant l'utilisation d'un modèle provenant du text donnée
     * @return bool
     */
	public function unset_template_text(){
		$this->_template_text = null;
		$this->_template_text_used = false;
		
		return true;
	}

    /**
     * Supprime la variable donnée du jeu
     * Boolean unset_vars(Mixed $var [,Mixed $var]) --- Mixed = String | Array of String value
     * @param null|string|array $var N nom de variable ou liste de nom de variable
     * @return bool
     */
	public function unset_vars($var=null){
		if(!is_null($var)){
			foreach(func_get_args() as $arg){
				if(is_array($arg)){
					foreach($arg as $name){
						if(is_string($name)){
							if(array_key_exists($name, $this->_vars)) unset($this->_vars[$name]);
						} else {
							$this->add_history(sprintf("unset_vars cannot unset var '%s'", $name), E_USER_WARNING);
						}
					}
				} else if(is_string($arg)){
					if(array_key_exists($arg, $this->_vars)) unset($this->_vars[$arg]);
				}
			}
			
			return true;
		} else {
			$this->throw_error(sprintf('Al least 1 argument (String or Array) must be given.', gettype($var)), E_USER_ERROR);
			return false;
		}
	}

    /**
     * Affiche le contenu du dossier executant la classe (permet de se positionner)
     */
	public function debugPath(){
		$currentDir = scandir('./');
		echo "<pre>";
		echo "Below, the neighborhood of the current folder where the class is executed : \n\n";
		print_r($currentDir);
		echo "</pre>";
	}

    /**
     * Affiche à l'écran le fichier rendu
     * @return bool
     */
	public function display(){
		/** Selon le mode de rendu, on affiche le fichier temporaire, soit on affiche de document final, déposé **/
		if($this->_render_type == 'temporary'){
			/** S'assurer que tout s'est bien passé **/
			if(file_exists($this->_temporary_folders_path.'/renders/'.$this->_output_name)){
				/** Affichage du fichier selon le traitement demandé **/
				switch($this->_utf8_read_treatment){
					case 'none':
						echo file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name);
					break;
					case 'encode':
						echo utf8_encode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
					break;
					case 'decode':
						echo utf8_decode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
					break;
				}
			} else {
				$this->throw_error('Template->display() with render_type="temporary" failed. The render has not been done or the file not exist. Use Template->render();', E_USER_ERROR);
			}
		} 
		/** Mode permanent **/
		else {
			if(file_exists($this->_output_directories[0].'/'.$this->_output_name)){
				/** Affichage du fichier selon le traitement demandé **/
				switch($this->_utf8_read_treatment){
					case 'none':
						echo file_get_contents($this->_output_directories[0].'/'.$this->_output_name);
					break;
					case 'encode':
						echo utf8_encode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
					break;
					case 'decode':
						echo utf8_decode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
					break;
				}
				
				if(!$this->_has_rendered){
					$this->add_history('Template->display() has retrieves the data of an existing document whereas there is no render which has been done.', E_USER_WARNING);
				}
			} else {
				$this->throw_error(sprintf('Template->display() with render_type="permanent" failed[%s]. The render has not been done or the file not exist. Use Template->render();', $this->_output_name), E_USER_ERROR);
			}
		}
		
		return true;
	} // Boolean display(Void)

    /**
     * Affiche à l'écran le manuel d'aide - Si disponible
     * @return bool
     */
	public function help(){
		if(file_exists($this->_absolute_path.'/Help/help.php') && file_exists($this->_absolute_path.'/Help/help.tpl')){
			require $this->_absolute_path.'/Help/help.php';
			
			help($this->_absolute_path);
			return true;
		} else {
			$this->throw_error('Template->help() :: The manual seems unavailable. Please check if the help folder and its files are present.', E_USER_ERROR);
			return false;
		}
	}

    /**
     * Affiche l'historique des erreurs non bloquante rencontrée
     * @param bool $reverse
     */
	public function show_historic($reverse=false){
		/** Déclaration des variables **/
		// Correspondance : valeur integer to valeur string (Codes erreurs)
		$error_level_str = Array(
			1 => 'E_ERROR', 
			2 => 'E_WARNING', 
			4 => 'E_PARSE', 
			8 => 'E_NOTICE', 
			16 => 'E_CORE_ERROR', 
			32 => 'E_CORE_WARNING', 
			64 => 'E_COMPILE_ERROR', 
			128 => 'E_COMPILE_WARNING', 
			256 => 'E_USER_ERROR', 
			512 => 'E_USER_WARNING', 
			1024 => 'E_USER_NOTICE', 
			2048 => 'E_STRICT', 
			4096 => 'E_RECOVERABLE_ERROR', 
			8192 => 'E_DEPRECATED', 
			16384 => 'E_USER_DEPRECATED', 
			32767 => 'E_ALL'
		);
		
		/** Début des sorties à l'aide de l'ouverture du champ PRE **/
		echo "<pre style='margin: 0 !important; padding: 0 !important; display: inline !important;'>";
		
		/** Si au moins une entrée existe, on affiche l'historique **/
		if(count($this->_historic) > 0){
			// Selon l'ordre demandé, lire le tableau dans un sens ou dans l'autre 
			$historic = ($reverse) ? array_reverse($this->_historic) : $this->_historic;
			
			foreach($historic as $index => $history){
				/** Récupération des données **/
				$message = $history['message'];
				//$msg_file = $history['file'];
				//$msg_line = $history['line'];
				$backtrace = $history['backtrace'];
				$err_lvl = $history['level'];
				$time = $history['time'];
				
				$backtrace_message = '';
				
				// Ne pas tenir compte de l'enregistrement indiquant l'appel de l'ajout de l'entrée dans l'historique
				array_shift($backtrace);
				foreach($backtrace as $i => $trace){
					$class = $trace['class'];
					$function = $trace['function'];
					$file = $trace['file'];
					$line = $trace['line'];
					
					$trace_message = sprintf("<b style='color: #336699;'>%s->%s</b> in <b>%s</b> on line <b style='color: #336699'>%d</b>;", $class, $function, $file, $line);
					
					$backtrace_message .= "<br /><b> • FROM  ::</b> $trace_message";
				}
				
				// Affichage
				echo sprintf("<b><br />#%04d. %s Message with Level <b style='color: #336699;'>'%s'</b> ::</b>\n • ERROR ::</b> <b style='color: red;'>%s</b>", ($index + 1), date('', $time),  $error_level_str[$err_lvl], $message);
				echo $backtrace_message.PHP_EOL;
			}
		}
		/** S'il est vide, alors on l'affiche comme vide. **/
		else {
			echo "<b style='color: #336699;'>The historic is empty.</b>";
		}
		
		/** Fin du champ de sortie PRE **/
		echo "</pre>";
	}

    /**
     * Emet une erreur PHP utilisateur
     * @param string $message
     * @param int $error_level
     */
	static function throw_error($message='', $error_level=E_USER_NOTICE){
		/** Taille manixmal des sorties : 1024 bit **/
		$traces = debug_backtrace();
		$backtrace_message = null;
		
		/** Commencer à 1 afin de ne pas tenir compte de la trace de throw_error **/
		for($i = (count($traces) - 1); $i > 0; $i--){
			$trace = $traces[$i];
			$class = $trace['class'];
			$function = $trace['function'];
			$file = $trace['file'];
			$line = $trace['line'];
			
			$trace_message = "<b style='color: #336699;'>$class->$function</b> in <b>$file</b> on line <b style='color: #336699'>$line</b>";
			
			$backtrace_message .= "<br /><b> • FROM  ::</b> $trace_message;";
		}
		
		$backtrace_message = "<b>BACKTRACE ::</b> $backtrace_message";
		
		/** Utiliser un PRE pour l'affichage soigné, mais sécurisé la dispo HTML si l'operateur de control d'erreur @ est utilisé **/
		echo "<pre style='margin: 0 !important; padding: 0 !important; display: inline !important;'>";
		trigger_error("$backtrace_message", E_USER_NOTICE);
		
		// ln = 63 > <b>MESSAGE ::</b>\n • ERROR ::</b> <b style='color: red;'>$message</b>
		if(mb_strlen($message) > (1024 - 56)){
			$message = substr($message, 0, (1024 - 63 - 5));
			$message .= '(...)';
		}
		
		trigger_error("<b>MESSAGE ::</b>\n • ERROR ::</b> <b style='color: red;'>$message</b>", $error_level);
		echo "</pre>";
	}

    /**
     * Ajoute dans l'historique l'erreur soumise
     * @param $message
     * @param $err_level
     * @param bool $debug_backtrace_provide_object
     */
	public function add_history($message, $err_level, $debug_backtrace_provide_object=false){
		$record_time = time();
		
		$this->_historic[] = Array(
			'time' => $record_time,
			'backtrace' => debug_backtrace($debug_backtrace_provide_object),	// Ne pas peuplé l'index OBJECT (trop de charge inutile)
			'message' => $message,
			//'file' => $file,
			//'line'=> $line,
			'level' => $err_level
		);
		
		if($this->_debug_mode) self::throw_error($message, $err_level);
	}

    /**
     * Fusionne les tableaux données sans tenir comptes des indexs numérique
     * @return array|bool
     */
	static function array_merge_index_recursive(){
		/** Controler les arguments **/
		if(func_num_args() < 2){
			Template::throw_error('At least two (2) arguments are required to merge in one array.', E_USER_WARNING);
			return false;
		}
		
		/** Controler le type des arguments **/
		foreach(func_get_args() as $akey => $avalue){
			if(gettype($avalue) !== 'array'){
				Template::throw_error(sprintf('Argument number %d has type "%s". Argument type expected is "array" to be merged.', $akey, gettype($avalue)), E_USER_WARNING);
				return false;
			}
		}
		
		/** Déclaration des functions de traitement récursive **/
		/** #1. Transcription des clé numérique en clé normal permettant la fusion **/
		if(!function_exists('index_to_key')){
			function index_to_key($array){
				$convert_array = Array();
				
				foreach($array as $key => $value){
					if(gettype($key) === 'integer'){
						$convert_array[strval("--{".$key."}--")] = (gettype($value) === 'array') ? index_to_key($value) : $value;
					} else {
						$convert_array[strval($key)] = (gettype($value) === 'array') ? index_to_key($value) : $value;
					}
				}
				
				return $convert_array;
			}
		}
		
		/** #2. Restauration des clé convertie en clé numérique **/
		if(!function_exists('key_to_index')){
			function key_to_index($array){
				$convert_array = Array();
				
				foreach($array as $key => $value){
					if(preg_match("#^--\{[0-9]+\}--$#", $key)){
						$key = str_replace("--{", "", $key);
						$key = str_replace("}--", "", $key);
							
						$convert_array[intval($key)] = (gettype($value) === 'array') ? key_to_index($value) : $value;
					} else {
						$convert_array[$key] = (gettype($value) === 'array') ? key_to_index($value) : $value;
					}
				}
				
				return $convert_array;
			}
		}
		
		/** Traitements **/
		$output_array = Array();
		
		foreach(func_get_args() as $akey => $avalue){
			$output_array = array_merge_recursive($output_array, index_to_key($avalue));
		}
		
		/** Renvois tu tableau fusionné **/
		return key_to_index($output_array);
	}

    /**
     * Supprime les commentaires du texte donné selon la syntaxe JavaScript
     * @param $text
     * @param bool $strip_blank
     * @return mixed
     */
	static function cleanse_js($text, $strip_blank=false){
		/** Supprimer les commentaires inline **/
		$text = preg_replace("#\/\/.*#m", "", $text);
		/** Supprimer les commentaires block inline **/
		$text = preg_replace("#\/\*.*\*\/#m", "&nbdel;", $text);
		/** Supprimer les commentaires block multiline **/
		$text = preg_replace("#\/\*(.|\n)*?\*\/#m", "&nbdel;", $text);
		/** Supprimer les &nbdel;**/
		$text = preg_replace("#&nbdel;\s*\n?#m", "", $text);
		
		return ($strip_blank) ? Template::strip_blank($text) : $text;
	}

    /**
     * Supprime les commentaires du texte donné selon la syntaxe SQL
     * @param $text
     * @param bool $strip_blank
     * @return mixed
     */
	static function cleanse_sql($text, $strip_blank=false){
		/** Supprimer les commentaires SQL inline **/
		$text = preg_replace("#(?<!<\!)--\s.*#m", "&nbdel;", $text); // Negative lookbehind = On dit "ne capture que les (--\s.*) qui ne sont pas précédés de (<\!)".
		/** Supprimer les commentaires block inline **/
		$text = preg_replace("#\/\*.*\*\/#m", "&nbdel;", $text);
		/** Supprimer les commentaires block multiline **/
		$text = preg_replace("#\/\*(.|\n)*?\*\/#m", "&nbdel;", $text);
		/** Supprimer les &nbdel;**/
		$text = preg_replace("#&nbdel;\s*\n?#m", "", $text);
		
		return ($strip_blank) ? Template::strip_blank($text) : $text;
	}

    /**
     * Supprime l'environnement de travail du moteur
     * @param bool $force Permet d'ignorer la demande de concervation des fichiers temporaire
     */
	public function cleansing_render_env($force=false){
		if(!$this->_keep_temp_file OR $force){
			//$this->remove_folder($this->_temporary_folders_path.'/buffers'); // a delete si recurisve
			//$this->remove_folder($this->_temporary_folders_path.'/renders'); // a delete si recurisve
			//$this->remove_folder($this->_temporary_folders_path.'/temps'); // a delete si recurisve
			$this->remove_folder($this->_temporary_folders_path);
		}
		
		$this->_render_env_exist = false;
	}

    /**
     * Supprime l'environnement de travail du moteur (Mode DOCUMENT_ROOT)
     * @param bool $force Permet d'ignorer la demande de concervation des fichiers temporaire
     */
	private function cleansing_render_env_root($force=false){
		if(!$this->_keep_temp_file OR $force){
			/** SEARCH FOR REAL PATH **/
			/**
				[DOCUMENT_ROOT] => /home/neoblast/LIBRARY
				[SCRIPT_FILENAME] => /home/neoblast/LIBRARY/PHP.Classes/Moteur.de.Template.Homemade.V.2.9/dev_class.php
				[SCRIPT_URL] => /PHP.Classes/Moteur.de.Template.Homemade.V.2.9/dev_class.php
				
				NGNIX unknow SCRIPT_URL
				
				FOR FULL PATH, use SCRIPT_FILENAME and remove filename
			**/
			/** > Retrieve full script path **/
			$full_path = $_SERVER['SCRIPT_FILENAME'];
			
			/** > Find position of last slash **/
			$last_slash_pos = strrpos($full_path, "/");
			
			/** > Strip the filename **/
			$full_path = substr($full_path, 0, $last_slash_pos);
			
			//$this->remove_folder($full_path.'/'.$this->_temporary_folders_path.'/buffers'); // a delete si recursive
			//$this->remove_folder($full_path.'/'.$this->_temporary_folders_path.'/renders'); // a delete si recursive
			//$this->remove_folder($full_path.'/'.$this->_temporary_folders_path.'/temps'); // a delete si recursive
			$this->remove_folder($full_path.'/'.$this->_temporary_folders_path);
		}
		
		$this->_render_env_exist = false;
	}
    /**
     * Ferme et purge du cache du fichier modèlé utilisé lors du rendu
     * @param $template_file
     */
	private function close_template_file($template_file){
		if(gettype($this->_templates_files_res[$template_file]) == 'resource'){
			fclose($this->_templates_files_res[$template_file]);
			$this->_template_file_res[$template_file] = null;
		} else {
			$this->throw_error(sprintf('Template->close_template_file() failed; The template "%s" is not opened.', $template_file), E_USER_ERROR);
		}
	}

    /**
     * Ferme le fichier temporaire de rendu
     * @return bool
     */
	private function close_temporary_render_file(){
		if(gettype($this->_temporary_render_file_res) == 'resource'){
			fclose($this->_temporary_render_file_res);
			$this->_temporary_render_file_res = null;
			$this->_temporary_file_openned = false;
		} else {
			$this->throw_error('Template->close_temporary_render_file() failed; Currently, there no file opened to close', E_USER_ERROR);
		}
		
		return true;
	}

    /**
     * Evalue l'expression conditionnel de l'instruction donnée
     * @param $instruction
     * @return bool
     */
	private function eval_conditions($instruction){
		/** Récupérer le test **/
		$start_index = strpos($instruction, '(');
		$end_index = strrpos($instruction, ')');
		$conditions = substr($instruction, ($start_index + 1), ($end_index - $start_index - 1));
		
		/** Préparation du test **/
		$resultat;
		$test = 'if('.$conditions.'){$resultat=true;}else{$resultat=false;}';
		
		/** Effectuer le test sous temporisation des sorties **/
		ob_start();
		eval($test);
		$eval = ob_get_contents();
		ob_end_clean();
		
		/** SI l'évaluation à échouée, alors on à une chaine de sortie **/
		if($eval !== ''){
			$resultat = false;
			
			$this->add_history(sprintf('Evaluatoin of condition "%s" failed : %s', $conditions, $eval), E_USER_WARNING);
		}	
		
		/** Retourner le test **/
		return $resultat;
	}

    /**
     * Recherche les instructions propriétaire et déclenche les processus correspondant
     * @param $buffer
     * @return bool
     */
	private function control_buffer($buffer){
		/** DECLARATION DES VARIABLES **/
			// RETURN VALUE
			$return = false;
			$assignation = null;
			$assign_value = null;
		
			// PATTERN
			$use_pattern = "#\s*{$this->_ins_open_tag}\s+USE((\s+)|(\())#i";
			$include_pattern = "#\s*{$this->_ins_open_tag}\s+INCLUDE_TEMPLATE((\s+)|(\())#i";
		
		/** CONTROLE SI INCLUDE **/
		if(preg_match($include_pattern, $buffer)){
			/** Si inclusion, alors executer "render()" mais**/
				/** On passe en recursion donc level ++ **/
				$this->_render_depth_level++;
			
				/** Evaluer l'instruction, car variable autorisée dans les includes **/
				//$buffer = $this->render_code($buffer, $this->_use_vars_ref[$this->_use_vars_level]);
				$buffer = $this->render_buffer($buffer);
			
				/** Déclencher le rendu du modèle inclus **/
				$this->render($this->get_input_param($buffer));
			
				/** Instruction trouvé et évaluée **/
				$return = true;
		}
		
		/** CONTROLE SI UTILISATION D'UN BLOCK BUFFERISE **/
		if(preg_match($use_pattern, $buffer)){
			/** Récupérer la cible **/
			$target = $this->get_input_param($buffer);
			$assign_type = null;
			
			if(preg_match('#\s*[-=]{1}>\s*#', $target)){
				preg_match('#[-=]{1}>#', $target, $assign_type);
				$assign_type = $assign_type[0];
				
				$params = preg_split('#\s*[-=]{1}>\s*#', $target);
				
				$target = $params[0];
				$assign_value = $params[1];
			}
			
			/** Trouver le fichier temporaire correspondant **/
			if(array_key_exists($target, $this->_buffered_names)){
				$tmp_file = $this->_buffered_names[$target];
			} else {
				$buffer = str_replace("<", "&lt;", $buffer);
				$buffer = str_replace(">", "&gt;", $buffer);
				
				$this->throw_error(sprintf("Template->control_buffer() has been stopped; You are trying to use an undefined block : %s on %s", $target, $buffer), E_USER_ERROR);
			}
			
			/** Identifier le process à déclencher **/
			$process = explode(".", $tmp_file);					// A l'aide de son extension
			$process = ".".$process[count($process) - 1];	// Sera toujours le dernier élément du tableau
			
			switch($process){
				case $this::BLOCK_EXT:
					$this->rendering_block($target, $tmp_file, $assign_type, $assign_value);
				break;
				
				case $this::IF_BLOCK_EXT:
					$this->rendering_if($tmp_file);
				break;
				
				case $this::PHP_BLOCK_EXT:
					$this->rendering_php($target, $tmp_file);
				break;
				
				case $this::TEMPLATE_EXT:
					/** A la maniere d'un include, on entre en recursion **/
					$this->_render_depth_level++;
					
					/** Déclencher le rendu du modèle déclaré **/
					$this->render($this->_temporary_folders_path.'/buffers/'.$tmp_file);
				break;
			}
			
			/** Instruction trouvé et évaluée **/
			$return = true;
		}
		
		return $return;
	}
    /**
     * Construit l'environnement de travail du moteur
     */
	private function make_render_env(){
		/** Si _temporary_repository est défini, alors y créer l'environnement dedans **/
		if($this->_temporary_repository != null){
			
			/** Création de _temporary_repository */
			@mkdir($this->_temporary_repository, 0777);
			
				/** Création du _temporary_directory **/
				@mkdir($this->_temporary_folders_path);
			
					/** Puis création des dossiers temporaire de travail **/
					@mkdir($this->_temporary_folders_path.'/buffers', 0777);
					@mkdir($this->_temporary_folders_path.'/renders', 0777);
					@mkdir($this->_temporary_folders_path.'/temps', 0777);
		}
		/** Sinon, l'environnement sera dans le dossier de dépot des rendu **/
		else{
			if(file_exists($this->_output_directories[0])){
			/** Création du _temporary_directory **/
			@mkdir($this->_temporary_folders_path);

				/** Puis création des dossiers temporaire de travail **/
				@mkdir($this->_temporary_folders_path.'/buffers', 0777);
				@mkdir($this->_temporary_folders_path.'/renders', 0777);
				@mkdir($this->_temporary_folders_path.'/temps', 0777);
			} else {
				$this->throw_error(sprintf("Template->make_render_env() failed; The output directory \"%s\" doesn't exist", $this->_output_directories[0]), E_USER_ERROR);
			}
		}
		$this->_render_env_exist = true;
	}

    /**
     * Dépose le fichier rendu vers le ou les dossiers données lorsque le mode est "permanent"
     */
	private function move_file(){
		foreach($this->_output_directories AS $key => $value){
			@copy($this->_temporary_folders_path.'/renders/'.$this->_output_name, $this->_output_directories[$key].'/'.$this->_output_name);
		}
	}

    /**
     * Ouvre le modèle et met en cache celui-ci
     * @param $template_file
     * @return bool
     */
	private function open_template_file($template_file){
		if(file_exists($template_file)){
			$this->_templates_files_res[$template_file] = fopen($template_file, 'r');
		} else {
			$this->throw_error(sprintf('Template->open_template_file() failed. The template "%s" doesn\'t not exist. Please check the path.', $template_file), E_USER_ERROR);
		}
		
		return true;
	}

    /**
     * Créer et ouvre le fichier de rendu temporaire
     * @return bool
     */
	private function open_temporary_render_file(){
		if($this->_output_name != null){
			if(file_exists($this->_temporary_folders_path.'/renders')){
				$this->_temporary_render_file_res = fopen($this->_temporary_folders_path.'/renders/'.$this->_output_name, 'w+');
				$this->_temporary_file_openned = true;
			} else {
				$this->throw_error('Template->open_temporary_render_file() failed.', E_USER_ERROR);
			}
		} else {
			$this->throw_error('Template->open_temporary_render_file() failed. The output name is undefined. Use Template->set_output_name($name);', E_USER_ERROR);
		}
		return true;
	}

    /**
     * Converti un chemin en nom de fichier
     * @param $template_file
     * @return mixed
     */
	private function path_file_to_name($template_file){
		return str_replace("/", ".", $template_file);
	}

    /**
     * Analyse le modèle et créer les différents fichiers pour le rendu
     * @param $template_file
     */
	private function prepare_buffers($template_file){
		/** INITIALISATION DES VARIABLES **/
			// NORMALES
				// Template Path with name to filename only;
				$prefixe_name = $this->path_file_to_name($template_file);
				
				// Fichier temporaire Maitre
				$master_buffer = fopen($this->_temporary_folders_path."/buffers/$prefixe_name".$this::MASTER_EXT, 'w+');
				
				// Ligne en cours de lecture dans le modèle 
				$processing_line = 0;
			
			// PATTERNS
				$begin_declare_pattern = "#\s*{$this->_ins_open_tag}\s+BEGIN_DECLARE((\s+)|(\())#";
				$begin_block_pattern = "#\s*{$this->_ins_open_tag}\s+BEGIN_BLOCK#";
				$begin_if_pattern = "#\s*{$this->_ins_open_tag}\s+IF((\s+)|(\())#";
				$begin_php_pattern = "#\s*{$this->_ins_open_tag}\s+BEGIN_PHP#";
				
				$end_declare_pattern = "#\s*{$this->_ins_open_tag}\s+END_DECLARE#";
				$end_block_pattern = "#\s*{$this->_ins_open_tag}\s+END_BLOCK#";
				$end_if_pattern = "#\s*{$this->_ins_open_tag}\s+ENDIF#";
				$end_php_pattern = "#\s*{$this->_ins_open_tag}\s+END_PHP#";
		
		
		/** LECTURE DU MODELE **/
		while($buffer = fgets($this->_templates_files_res[$template_file])){
			$processing_line++;
			/** -------------------------------------------------------------------------------- **
			/** ---									START DETECTION STEP										--- **
			/** -------------------------------------------------------------------------------- **/
			/** Selon le type de block, l'extension et le type change **/
			$block_detected = false;
			$block_extension;
			$block_type;
			$block_name;
			$block_ins_operator = null;
			$block_ins_value = null;
			$block_data = null;
			
			
			// LES "BLOCK"
			if(preg_match($begin_block_pattern, $buffer)){
				/** Récupération des données propre au type du block identifié **/
				$block_detected = true;
				$block_extension = $this::BLOCK_EXT;
				$block_type = 'block';
				
				$block_data = $this->read_block_instruction($buffer);
				
				$block_name = $block_data['name'];
				$block_ins_operator = $block_data['operator'];
				$block_ins_value = $block_data['value'];
				
				/** Validation de l'instruction **/
				/** Si block anonyme sans paramètres **/
				/** Si paramètres sans valeur **/
				if($block_name === null && $block_ins_operator === null){
					//// ------------------------------------------------------ ////
					//// --- Ajouter un stack dans la variable show_warning --- ////
					//// ------------------------------------------------------ ////
					$this->add_history(sprintf('The anonymous block has no specific data to use. [Template : %s, Line: %d]', $template_file, $processing_line), E_USER_WARNING);
				}  
				else if ($block_ins_operator !== null && $block_ins_value === null){
					//// ------------------------------------------------------ ////
					//// --- Ajouter un stack dans la variable show_warning --- ////
					//// ------------------------------------------------------ ////
					$this->add_history(sprintf('Missing value for parameter %s for block %s. [Template: %s, Line: %d]', $block_ins_operator, $block_name, $template_file, $processing_line), E_USER_WARNING);
				}
				
				/** Dans le cas d'un block anonyme, lui donner un nom unique **/
				if($block_name === null){
					do {
						$block_name = sha1($template_file.'.'.time().'.'.$processing_line);
					} while (array_key_exists($block_name, $this->_buffered_names));
				}
			} 
			// LES "IF_BLOCK"
			else if(preg_match($begin_if_pattern, $buffer)) {
				/** Récupération des données propre au type du block identifié **/
				$block_detected = true;
				$block_extension = $this::IF_BLOCK_EXT;
				$block_type = 'if';
				$block_name = $this->get_if_block_name($buffer);
			} 
			// LES "PHP_BLOCK"
			else if (preg_match($begin_php_pattern, $buffer)){
				/** Récupération des données propre au type du block identifié **/
				$block_detected = true;
				$block_extension = $this::PHP_BLOCK_EXT;
				$block_type = 'php';
				$block_data = $this->read_block_instruction($buffer);
				$block_name = $block_data['name'];
			} 
			// LES "DECLARE_BLOCK"
			else if (preg_match($begin_declare_pattern, $buffer)){
				/** Determiner à quelle famille appartient le block en cours de déclaration **/
				$input_param = $this->get_input_param($buffer);
				
				/** Explode to FAMILY:PARAM_TO_USE **/
				$input_param = explode("->", $input_param);
				
				$block_family = strtolower($input_param[0]);
				$block_name = $this->get_if_block_name($buffer); // Méthode qui gère la detection de nom à l'aide du mot clé AS
				
				/** Donnée relative au block identifié **/
				switch($block_family){
					case 'block':
						$block_extension = $this::BLOCK_EXT;
					break;
					case 'if':
						$block_extension = $this::IF_BLOCK_EXT;
						/** Particularité au block IF, l'instruction doit être enregistrée **/
						$buffer = "{$this->_ins_open_tag_def} IF ".$input_param[1]." AS $block_name {$this->_ins_close_tag_def}\n"; // Ré-écriture du buffer
					break;
					case 'php':
						$block_extension = $this::PHP_BLOCK_EXT;
					break;
					case 'template':
						$block_extension = $this::TEMPLATE_EXT;
					break;
				}
				
				/** Block détecté **/
				$block_detected = true;
				/** Type de block "declare" **/
				$block_type = 'declare';
			}
			
			
			/** Si block détecté, procéder aux opérations suivantes **/
			if($block_detected){
				/** Vérifier qu'il n'existe pas déjà **/
				if(!array_key_exists($block_name, $this->_buffered_names)){
					
					/** Composition du nom de fichier temporaire **/
					$tmp_file_name = $prefixe_name.'.'.$block_name.$block_extension;
					
					/** Enregistrement du block **/
					$this->_buffered_names[$block_name] = $tmp_file_name;
					
					/** Création du fichier temporaire dédié **/
					$this->_buffered_files_res[$block_name] = fopen($this->_temporary_folders_path."/buffers/$tmp_file_name", 'w+');
				} else {
					$this->throw_error(sprintf('Template->prepare_buffers() failed; The rendering failed because the name "%s" is already used inside template %s and it must be unique.', $block_name, $template_file), E_USER_ERROR);
				}
				
				
				/** Création de l'instruction de remplacement si ce n'est pas une déclaration **/
				if($block_type !== 'declare'){
					$instruction_ext = null;
					
					if($block_ins_operator !== null){
						switch(strtoupper($block_ins_operator)){
							case 'WITH':
								$instruction_ext = "->$block_ins_value";
							break;
							case 'EXTEND WITH':
								$instruction_ext = "=>$block_ins_value";
							break;
							default:
								$instruction_ext = null;
							break;
						}
					}
					
					$instruction = "{$this->_ins_open_tag_def} USE ($block_name$instruction_ext) {$this->_ins_close_tag_def}\n";
					
					/** Ecriture dans le fichier approprié **/
					if($this->_buffered_flow_level < 0){
						fputs($master_buffer, $instruction);
					} else {
						fputs($this->_buffered_files_res[$this->_buffered_flow_records[$this->_buffered_flow_level]['name']], $instruction);
					}
				}
				
				/** Mise à jour des flags **/
				$this->_buffered_flow_level++;
				$this->_buffered_flow_records[$this->_buffered_flow_level] = Array(
					'name' => $block_name,
					'type' => $block_type
				);
			}// Prévoir un flag skip end_detection => optimi
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---									END DETECTION STEP										--- **
			/** -------------------------------------------------------------------------------- **/
			/** La recherche d'une instruction de fin n'a lieu que si on est dans un block X (quelque soit son type) **/
			if($this->_buffered_flow_level >= 0){
				/** identifier quel process de détection effectuer **/
				$process_type = $this->_buffered_flow_records[$this->_buffered_flow_level]['type'];
				$process_name = $this->_buffered_flow_records[$this->_buffered_flow_level]['name'];
				
				/** Determiner le end tag correspondant **/
				switch($process_type){
					case 'block':
						$end_tag = "END_BLOCK";
					break;
					case 'if':
						$end_tag = "ENDIF";
					break;
					case 'php':
						$end_tag = "END_PHP";
					break;
					case 'declare':
						$end_tag = "END_DECLARE";
					break;
				}
				
				/** Composition du pattern de fin correspondant **/
				$end_pattern = "#\s*{$this->_ins_open_tag}\s+$end_tag#";
				
				/** Rechercher si la fin est atteinte **/
				if(preg_match($end_pattern, $buffer)){
					/** Fermer le fichier temporaire **/
					fclose($this->_buffered_files_res[$process_name]);
					
					/** Remonte d'un cran dans le flux **/
					$this->_buffered_flow_level--;
				}
			}
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---										OUTPUTS STEP											--- **
			/** -------------------------------------------------------------------------------- **/
			/** Enregistrer le buffer s'il ne s'agit pas d'une instructions sauf exception **/
			/** PARTICULARITE Block_IF - Balise d'ouverture à enregister **/
			if(
				!preg_match($begin_block_pattern, $buffer) AND
				!preg_match($end_block_pattern, $buffer) AND
				!preg_match($end_if_pattern, $buffer) AND
				!preg_match($begin_php_pattern, $buffer) AND
				!preg_match($end_php_pattern, $buffer) AND
				!preg_match($begin_declare_pattern, $buffer) AND
				!preg_match($end_declare_pattern, $buffer)
			){
				if($this->_buffered_flow_level < 0){
					fputs($master_buffer, $buffer);
				} else {
					fputs($this->_buffered_files_res[$this->_buffered_flow_records[$this->_buffered_flow_level]['name']], $buffer);
				}
			}
		}
		
		/** FERMETURE DU MASTER UNE FOIS TERMINEE **/
		fclose($master_buffer);
	}

    /**
     * Découpe l'instruction block afin d'en retenir les informations utiles
     * @param $instruction
     * @return array
     */
	private function read_block_instruction($instruction){
		/**
			
			Role : Identification toujours juste quelque soit la saisie :
			
			<!-- BEGIN_BLOCK NAME -->					// Code attendu
			<!-- BEGIN_BLOCK  NAME --> 				// Code approuvé, erreur d'espace
			 <!-- BEGIN_BLOCK NAME --> 				// Espace en début d'instruction
			<!-- BEGIN_BLOCK NAME WITH xxx -->				// Anonyme avec jeu de donnée
			<!-- BEGIN_BLOCK NAME EXTEND WITH xxx -->	// Anonyme avec extention de donnée
			<!-- BEGIN_BLOCK WITH xxx -->				// Anonyme avec jeu de donnée
			<!-- BEGIN_BLOCK EXTEND WITH xxx -->	// Anonyme avec extention de donnée
			
			<!-- BEGIN_PHP NAME -->	// Code attendu
			<!-- BEGIN_PHP  NAME --> // Code approuvé, erreur d'espace
			 <!-- BEGIN_PHP NAME --> // Espace en début d'instruction
			<!-- BEGIN_PHP -->
		
		**/
		/** Déclaration des variables **/
		$name = null;
		$operator = null;
		$value = null;
		
		/** Supprimer LF RF **/
		$instruction = str_replace("\r", '', $instruction);
		$instruction = str_replace("\n", '', $instruction);
		$instruction = str_replace("\t", '', $instruction);
		
		/** Supprimer tout les espace avant **/
		$instruction = preg_replace('#^\s+#', '', $instruction);
		
		/** Pattern de découpage **/
		// Note ?i permet de rendre la suite insensible a la casse, puis ?-i retire le modifier i
		$is_pattern_start = "#{$this->_ins_open_tag}\s+(?i)BEGIN_(BLOCK|PHP)(?-i)\s+#";
		//$is_pattern_params = "#\s*(?i)(EXTEND\s+)?(WITH\s+\w+)(?-i)\s+-->$#";
		$is_pattern_params = "#\s*(?i)((EXTEND\s+)?WITH\s+\w*)?(?-i)\s*{$this->_ins_close_tag}$#";
		
		/** #1. Obtention du nom **/
			// Supression du début de l'instruction
			$name = preg_replace($is_pattern_start, '', $instruction);
			// Suppresion de tout le reste
			$name = preg_replace($is_pattern_params, '', $name);
			// Affinage
			$name = ($name === '') ? null : $name;
		
		/** #2. Obtention du paramètres **/
			// Supression du début de l'instruction
			$operator = preg_replace($is_pattern_start, '', $instruction);
			// Si un nom existe, on le supprime puisqu'il est connu
			$operator = ($name !== null) ? preg_replace("#\s*$name\s*#", '', $operator) : $operator;
			// Supprimer la valeur du paramètre
			$operator = preg_replace("#\s*\w*(?i)(?<!WITH)(?-i)\s*{$this->_ins_close_tag}$#", '', $operator);
			// Supprimer la balise fermant en cas d'absence de nom et de paramètre 
			$operator = preg_replace("#\s*{$this->_ins_close_tag}$#", '', $operator);
			// Affinage
			$operator = ($operator === '') ? null : $operator;
		
		/** #3. Obtention de la valeur **/
			// Supression du début de l'instruction
			$value = preg_replace($is_pattern_start, '', $instruction);
			// Si un nom existe, on le supprime puisqu'il est connu
			$value = ($name !== null) ? preg_replace("#\s*$name\s*#", '', $value) : $value;
			// Si la partie paramètre existe, on la supprime puisque connue
			$value = ($operator !== null) ? preg_replace("#\s*$operator\s*#", '', $value) : $value;
			// Supprimer la balise fermant en cas d'absence de nom et de paramètre 
			$value = preg_replace("#\s*{$this->_ins_close_tag}$#", '', $value);
			// Affinage
			$value = ($value === '') ? null : $value;
		
		/** Renvois des de la composition de l'instruction **/
		return Array(
			//'name' => "#$name#",
			//'operator' => "#$operator#",
			//'value' => "#$value#"
			'name' => $name,
			'operator' => $operator,
			'value' => $value
		);
	}

    /**
     * Supprime le dossier spécifié
     * @param $folder_path
     * @return bool|void
     */
	public function remove_folder($folder_path){
		/** Se positionner sur le dossier **/
		$ouverture=@opendir($folder_path);
		
		/** Si l'ouverture à échouée, le dossier n'existe pas ou n'est pas un dossier **/
		if (!$ouverture) return;
		
		/** Lire son contenu **/
		while($fichier=readdir($ouverture)) {
			/** Si se sont les référence UNIX, on skip **/
			if ($fichier == '.' || $fichier == '..') continue;
			
			/** Si c'est un dossier, on entre en recusion **/
			if (is_dir($folder_path."/".$fichier)) {
				$r=$this->remove_folder($folder_path."/".$fichier);
				//if (!$r) return false;
			}
			/** Sinon c'est un fichier, on le supprimer **/
			else {
				$r=@unlink($folder_path."/".$fichier);
				//if (!$r) return false;
			}
		}
		
		/** Ferme le pointeur **/
		closedir($ouverture);
		
		/** Enfin on supprime le dossier**/
		$r=@rmdir($folder_path);
		
		//if (!$r) return false;
		return true;
	}

    /**
     * Déclenche l'ensemble des procéssus pour généer le document
     * @param null $template_file
     * @return $this
     */
	public function render($template_file=null){
		if($this->_start_var_delim !== ''){
			/** -------------------------------------------------------------------------------- **
			/** ---								DECLARATION DES VARIABLES									--- **
			/** -------------------------------------------------------------------------------- **/
			/** Identifier le modèle source **/
			/** Si la source est définie (soit template_text assmilé à file ou template_file) **/
			if($this->_template_file !== null){
				/** Si render est executé sans paramètre, c'est l'appel d'origine et donc on utilise le template source/racine/root **/
				$template_file = ($template_file === null) ? $this->_template_file : $template_file;
			} else {
				$this->throw_error('Template->render() failed; The source template is not defined. Use Template->set_template_file($template) or Template->set_template_text() before;', E_USER_ERROR);
			}
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---						CREATION DE L'ENVIRONNEMENT DE TRAVAIL							--- **
			/** -------------------------------------------------------------------------------- **/
			if(!$this->_render_env_exist){
				$this->make_render_env();
			}
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---						OUVERTURE DES FICHIERS DE TRAVAIL								--- **
			/** -------------------------------------------------------------------------------- **/
				/** > Si le fichier d'ecriture n'est pas ouvert, l'ouvrir **/
				if(!$this->_temporary_file_openned){
					$this->open_temporary_render_file();
				}
					
				/** > Ouvrir le modèle **/
				$this->open_template_file($template_file);
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---						BUFFERISATION DU MODELE ET DE SON CONTENU						--- **
			/** -------------------------------------------------------------------------------- **/
				/** Si l'execution de render est l'execution source (pas une récursion) alors RAZ en cas de re-rendus **/
				/** Sinon, l'inclusion de template (meme process) va reset les block temporisé et il y aura écrasement de fichier temporisé **/
				if($this->_render_depth_level < 0){
					/** Tableau de référencement des noms de block **/
					$this->_buffered_names = Array();	// Purge en cas de re-rendu
					
					/** Au départ, c'est les variables racines à utiliser - Niveau d'utilisation évolue en répétition de block **/
					$this->_use_vars_level = 0;
					$this->_use_vars_ref[$this->_use_vars_level] = $this->_vars;	// Pointeur vers l'ensemble de variable à utilisé // Niveau 0 = $this->_vars
				}
			
				/** Bufferisation **/
				$this->prepare_buffers($template_file);
			
				/** Fermeture du template **/
				$this->close_template_file($template_file);
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---										RENDERISATION											--- **
			/** -------------------------------------------------------------------------------- **/
				/** Ouverture du fichier temporaire maitre **/
				$prefixe_name = $this->path_file_to_name($template_file);
				$master = fopen($this->_temporary_folders_path."/buffers/$prefixe_name".$this::MASTER_EXT, 'r');
			
				/** Lecture du fichier **/
				while($buffer = fgets($master)){
					/** Soumettre le buffer au controler - Si instruction, le controller déclenche les méthodes appropriée et retourne vrai s'il y à une instruction **/
					/** Dans ce cas, il ne faut pas enregistrer cette ligne **/
					if(!$this->control_buffer($buffer)){
						$this->store_buffer($this->render_buffer($buffer));
					} else {
						/** Tout de même enregistrer le EOL (essentiel dans les textes bruts style event-stream) **/
						$this->store_buffer(PHP_EOL);
					}
				}
			
			
			/** -------------------------------------------------------------------------------- **
			/** ---					FINALISATION - LECTURE DU FICHIER TERMINEE						--- **
			/** -------------------------------------------------------------------------------- **/
				/** FERMER LE POINTEUR SUR LE FICHIER TEMPORAIRE **/
				fclose($master);
			
				/** SI ON EST A L'EXECUTION INITIALE DE RENDER (non pas dans une recursion) **/
				/** ALORS LA RENDERISATION EST TERMINEE **/
				if($this->_render_depth_level < 0){
					/** Fermeture du fichier de destination **/
					$this->close_temporary_render_file();
					
					/** Si le render_type est "permanent", alors procéder à la dépose du fichier **/
					if($this->_render_type === 'permanent'){
						$this->move_file();
					}
				} 
				/** SINON LA RECURSION EST TERMINEE, ON REMONTE D'UN CRAN **/
				else {
					$this->_render_depth_level--;
				}
		} else {
			$this->throw_error('Template->render() failed. The vars delimiter is not defined. Use Template->set_vars_delim($delim);', E_USER_ERROR);
		}
		
		// Il y à bien eu un rendu
		$this->_has_rendered = true;
		return $this;
	}

    /**
     * Génère le contenu final du block donné (répétable)
     * @param $target
     * @param $tmp_file
     * @param null $assign_type
     * @param null $assign_value
     */
	private function rendering_block($target, $tmp_file, $assign_type=null, $assign_value=null){
		/** Ouvrir la source **/
		$block_file = fopen($this->_temporary_folders_path."/buffers/$tmp_file",'r');
		
		/** Définir le nouveau niveau de variable à utiliser **/
		$this->_use_vars_level++;
		
		/** Déclaration des tableaux de données **/
		$default_data = Array();
		$extend_data = Array();
		
		/** Si le jeu de donnée par défault existe, on le récupère **/
		// On le récupère dans le jeu de donnée du niveau du dessus
		if(isset($this->_use_vars_ref[$this->_use_vars_level - 1][$target]) && gettype($this->_use_vars_ref[$this->_use_vars_level - 1][$target]) === 'array'){
			$default_data = $this->_use_vars_ref[$this->_use_vars_level - 1][$target];
		}
			
		/** Si un jeu de donnée est spécifié on l'utilise aussi **/
		if($assign_value !== null){
			/** Contrôler l'existence du jeu de donnée **/
			if(array_key_exists($assign_value, $this->_vars)){
				$extend_data = $this->_vars[$assign_value];
			}
			/** Notification **/
			else {
				$this->add_history(sprintf("Extended data '%s' does not exist; An empty array use instead.", $assign_value), E_USER_WARNING);
			}
			
		}
		
		/** Fusion des jeu de donnée **/
		//$this->_use_vars_ref[$this->_use_vars_level] = self::array_merge_index_recursive($default_data, $extend_data);
		//echo "DEF:"; print_r($default_data);
		//echo "EXT:"; print_r($extend_data);
		
		switch($assign_type){
			// WITH
			case '->':
				$this->_use_vars_ref[$this->_use_vars_level] = self::array_merge_index_recursive($default_data, $extend_data);
			break;
			// EXTEND WITH
			case '=>':
				$this->_use_vars_ref[$this->_use_vars_level] = array_merge_recursive($default_data, $extend_data);
			break;
			// Si non défini alors une fusion recursive convient puisqu'aucun jeu de donnée n'est demandé
			default:
				$this->_use_vars_ref[$this->_use_vars_level] = self::array_merge_index_recursive($default_data, $extend_data);
			break;
		}
		
		//echo "MER:"; print_r($this->_use_vars_ref[$this->_use_vars_level]);
		
		
		/** Operer autant de fois que nécessaire, le fichier temporaire **/
		/** Uniquement si les données envoyé sont bien dans un tableau **/
		if(gettype($this->_use_vars_ref[$this->_use_vars_level]) === 'array'){
			$memorize_vars = $this->_use_vars_ref[$this->_use_vars_level];
			
			for($i = 0; $i < count($memorize_vars); $i++){
				/** Pour cette iteration sans changer de niveau de variable on met à jour la reference **/
				$this->_use_vars_ref[$this->_use_vars_level] = $memorize_vars[$i];
				
				/** Repositionne le curseur en début de fichier **/
				fseek($block_file, 0);
				
				/** Lecture du fichier **/
				while($buffer = fgets($block_file)){
					/** Soumettre le buffer au controler - Si instruction, le controller déclenche les méthodes appropriée et retourne vraie s'il y à une instruction **/
					/** Dans ce cas, il ne faut enregistrer cette ligne **/
					if(!$this->control_buffer($buffer)){
						$this->store_buffer($this->render_buffer($buffer));
					} else {
						/** Tout de même enregistrer le EOL (essentiel dans les textes bruts style event-stream) **/
						$this->store_buffer(PHP_EOL);
					}
				}
			}
		}
		
		/** Lorsque les traitements du block sont terminées, alors on remonte dans le niveau de variable à utiliser **/
		$this->_use_vars_level--;
	}

    /**
     * Génère le contneu final du block conditionnel donné
     * @param $tmp_file
     */
	private function rendering_if($tmp_file){
		/** Déclaration des variables **/
			// PATTERNS
			$begin_if_pattern = "#\s*{$this->_ins_open_tag}\s+IF((\s+)|(\())#";
			$else_if_pattern = "#\s*{$this->_ins_open_tag}\s+ELSEIF((\s+)|(\())#";
			$else_pattern = "#\s*{$this->_ins_open_tag}\s+ELSE\s+#";
		
			// FLAGS
			$part_approved = false;
		
		/** Ouvrir la source **/
		$block_file = fopen($this->_temporary_folders_path."/buffers/$tmp_file",'r');
		
		/** Lecture du fichier **/
		while($buffer = fgets($block_file)){
			/** Indique si on enregistre le buffer **/
			$record_buffer = true;
			
			/** Recherche des instructions propre au block conditionnel **/
				/** Recherche du debut de test **/
				if(preg_match($begin_if_pattern, $buffer)){
					/** Rendre l'instruction puis l'evaluer **/
					/** Evaluer la condition de l'instruction **/
					if($this->eval_conditions($this->render_buffer($buffer))){
						$part_approved = true;
						$record_buffer = false; // Indique de ne pas enregistrer l'instruction
					}
				}
				
				/** Recherche d'un autre ensemble conditionnel **/
				if(preg_match($else_if_pattern, $buffer)){
					/** Si l'instruction ELSE_IF est trouvée : **/
					/** Si $part_approved vaut vraie, alors un ensemble à été approuvé et écris, fin du block cdn **/
					if($part_approved){
						break;
					}
					/** Sinon, evaluer les conditions **/
					else {
						if($this->eval_conditions($this->render_buffer($buffer))){
							$part_approved = true;
							$record_buffer = false;
						}
					}
				}
				
				/** Recherche du cas ELSE (sinon) **/
				if(preg_match($else_pattern, $buffer)){
					/** Si l'instruction ELSE est trouvée : **/
					/** Si $part_approved vaut vraie, alors un ensemble à été approuvé et écris, fin du block cdn **/
					if($part_approved){
						break;
					}
					/** Sinon, evaluer les conditions **/
					else {
						$part_approved = true;
						$record_buffer = false;
					}
				}
				
			
			/** Si la partie est approuvé et qu'on est autorisé à écrire **/
			if($record_buffer AND $part_approved){
				/** Vérifier que ce n'est pas une instruction **/
				if(!$this->control_buffer($buffer)){
					$this->store_buffer($this->render_buffer($buffer));
				}
			}
		} // END_WHILE
		
	}
    /**
     * Génère le contneu du block PHP donnée
     * @param $target
     * @param $tmp_file
     */
	private function rendering_php($target, $tmp_file){
		/** Ouverture sur $_PHP **/
		global $_PHP;
		
		/** Ouvrir la source **/
		$block_file = fopen($this->_temporary_folders_path."/buffers/$tmp_file",'r');
		
		/** D'abord render le block PHP au niveau de variable en cours **/
		$tmp_file_php_to_eval = fopen($this->_temporary_folders_path."/temps/$tmp_file.eval", "w+");
		
		while($buffer = fgets($block_file)){
			/** Vérifier que ce n'est pas une instruction **/
			if(!$this->control_buffer($buffer)){
				fputs($tmp_file_php_to_eval, $this->render_buffer($buffer));
			}
		}
		
		/** Fermeture du fichier **/
		fclose($tmp_file_php_to_eval);
		
		/** L'évaluer en tant que PHP **/
		$code = file_get_contents($this->_temporary_folders_path."/temps/$tmp_file.eval");
		ob_start();
		$eval = eval($code);
		$evaluated = ob_get_contents();
		ob_end_clean();
		
		/** Dans un cas possible où l'utilisateur compose une variable qui souhaite remplacer **/
		/** Finalise par le remplacement des variables existante **/
		$this->store_buffer($this->render_buffer($evaluated));
	}

    /**
     * Procède au remplacement des variables dans l'instruction donnée
     * @param $buffer
     * @return mixed
     */
	private function render_buffer($buffer){
		/** Déclaration des patterns de detections **/
		//$vars_bridged_pattern = '#['.$this->_var_delim.']{2}[a-zA-Z0-9_\-@&\'":\.]+['.$this->_var_delim.']{2}#';
		//$vars_pattern = '#['.$this->_var_delim.']{1}[a-zA-Z0-9_\-@&\'":\.]+['.$this->_var_delim.']{1}#';
		$vars_bridged_pattern = '#('.$this->_start_var_delim.'){2}[a-zA-Z0-9_\-@&\'":\.]+('.$this->_end_var_delim.'){2}#';
		$vars_pattern = '#('.$this->_start_var_delim.'){1}[a-zA-Z0-9_\-@&\'":\.]+('.$this->_end_var_delim.'){1}#';
		
		
		/** Traitement dédié aux blocks appelant une variable simple (forcé sur $this->_vars) **/
		/** Capturer toutes les variables (double délim) de la chaines **/
		preg_match_all($vars_bridged_pattern, $buffer, $matches);
		
		/** Parcourir les variables enregistrées **/
		foreach($matches[0] as $key => $value){
			/** Obtenir le nom à proprement parler**/
			$var_name = $value;
			$var_name = preg_replace('#('.$this->_start_var_delim.'){2}#', "", $var_name);
			$var_name = preg_replace('#('.$this->_end_var_delim.'){2}#', "", $var_name);
			
			/** Si la clé existe alors procéder au remplacement **/
			if(array_key_exists($var_name, $this->_vars)){
				// Si render_buffer remplace avec du boolean et que la valeur va dans eval_condition, il est impossible d'évaluer un boolean concaténer en string
				$replace_value = (is_bool($this->_vars[$var_name])) ? (($this->_vars[$var_name]) ? 'true' : 'false') : $this->_vars[$var_name];
				$buffer = str_replace($value, $replace_value, $buffer);
			}
		}
		
		
		/** Traitement normal pour tout les autres (blocks compris) ($use_var) **/
		/** Capturer toutes les variables de la chaines **/
		$matches = null; // RAZ
		preg_match_all($vars_pattern, $buffer, $matches);
		
		/** Parcourir les variables enregistrées **/
		foreach($matches[0] as $key => $value){
			/** Obtenir le nom à proprement parler**/
			$var_name = $value;
			$var_name = preg_replace('#('.$this->_start_var_delim.'){1}#', "", $var_name);
			$var_name = preg_replace('#('.$this->_end_var_delim.'){1}#', "", $var_name);
			
			/** Si la clé existe alors procéder au remplacement **/
			if(array_key_exists($var_name, $this->_use_vars_ref[$this->_use_vars_level])){
				$replace_value = (is_bool($this->_use_vars_ref[$this->_use_vars_level][$var_name])) ? (($this->_use_vars_ref[$this->_use_vars_level][$var_name]) ? 'true' : 'false') : $this->_use_vars_ref[$this->_use_vars_level][$var_name];
				$buffer = str_replace($value, $replace_value, $buffer);
			}
		}
		
		/** Renvoies la chaine convertie **/
		return $buffer;
	}

    /**
     * Envoi le document par mail aux destinataires configuré au préalable
     * @return bool
     */
	public function sendMail(){
		/** SI LES DESTINATAIRES SONT DEFINIE **/
		if($this->_mail_recipients != null){
			/** SI L'OBJECT EST DEFINIE **/
			if($this->_mail_subject != null){
				/** SI L'ADRESSE DE L'EMETTEUR EST DEFINIE **/
				if($this->_mail_sender != null){
					/** HEADER DEFINITION **/
					$headers = "From: ".$this->_mail_sender_name."<".$this->_mail_sender.">\n";
					$headers .= "X-Mailer: PHP ".phpversion()."\n";
					$headers .= "Reply-To:".$this->_mail_sender."\n";
					$headers .= "Organization: neoblaster.fr\n";
					$headers .= "X-Priority: 3 (Normal)\n";
					$headers .= "Mime-Version: 1.0\n";
					$headers .= "Content-Type: text/html; charset=\"UTF-8\"";
					$headers .= "Content-Transfer-Encoding: 8bit\n";
					$headers .= "Date:" . date("D, d M Y h:s:i" ) . " +0300\n";	

					/** CONTENT DEFINITION**/	
					switch($this->_utf8_read_treatment){
						case 'none':
							if($this->_render_type == 'temporary'){
								$mailContent = file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name);
							} else {
								$mailContent = file_get_contents($this->_output_directories[0].'/'.$this->_output_name);
							}
						break;
						case 'encode':
							if($this->_render_type == 'temporary'){
								$mailContent = utf8_encode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
							} else {
								$mailContent = utf8_encode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
							}
						break;
						case 'decode':
							if($this->_render_type == 'temporary'){
								$mailContent = utf8_decode(file_get_contents($this->_temporary_folders_path.'/renders/'.$this->_output_name));
							} else {
								$mailContent = utf8_decode(file_get_contents($this->_output_directories[0].'/'.$this->_output_name));
							}
						break;
					}		
					
					/** SEND MAIL **/
					mail($this->_mail_recipients, $this->_mail_subject, $mailContent, $headers);
				} else {
					$this->throw_error('Template->sendMail() failed. The mail sender is undefined. Use Template->set_mail_sender($sender);', E_USER_ERROR);
				}
			} else {
				$this->throw_error('Template->sendMail() failed. The mail subject is undefined. Use Template->set_mail_subject($subject);', E_USER_ERROR);
			}
		} else {
			$this->throw_error('Template->sendMail() failed. The mails recipients are undefined. Use Template->set_mail_recipients(mail(s));', E_USER_ERROR);
		}	
		
		return true;
	} // Boolean sendMail(Void)

    /**
     * Redirige la ligne analysé vers le fichier qui convient selon sa position dans l'analyse
     * @param $buffer
     */
	private function store_buffer($buffer){
		/** INSERTION DANS LE FICHIER DE SORTIE **/
		switch($this->_utf8_write_treatment){
			case 'none':
				fputs($this->_temporary_render_file_res, $buffer);
			break;
			case 'encode':
				fputs($this->_temporary_render_file_res, utf8_decode($buffer));
			break;
			case 'decode':
				fputs($this->_temporary_render_file_res, utf8_encode($buffer));
			break;
		}
	}
    /**
     * Supprime les caractères d'espacement Carriage Return, Line Feed & Tabulation
     * @param $text
     * @return mixed
     */
	static function strip_blank($text){
		$text = str_replace("\t", "", $text);
		$text = str_replace("\n", "", $text);
		$text = str_replace("\r", "", $text);
		
		return $text;
	}

    /**
     * Met à jour l'emplacement des fichiers temporaires
     */
	private function update_temporary_folders_path(){
		$this->_temporary_folders_path = ($this->_temporary_repository != null) 
			? $this->_temporary_repository.'/'.$this->_temporary_directory 
			: $this->_output_directories[0].'/'.$this->_temporary_directory;
	}

    /**
     * Mise à jour du jeu de donnée existant dans qu'il soit écrasé (Update / Add)
     * @param array $vars
     * @return bool
     */
	public function update_vars($vars){
		if(is_array($vars)){
			foreach($vars as $var_name => $var_value){
				$this->_vars[$var_name] = $var_value;
			}
			
			return true;
		} else {
			$this->throw_error(sprintf('update_vars() expects parameter 1 to be array, %s given in.', gettype($vars)), E_USER_ERROR);
			return false;
		}
	}
    /**
     * Inverse les valeurs boolean des variables données
     * Boolean unset_vars(Mixed $var [,Mixed $var]) --- Mixed = String | Array of String value
     * @param null $var
     * @return bool
     */
	public function xor_vars($var=null){
		if(!is_null($var)){
			foreach(func_get_args() as $arg){
				if(is_array($arg)){
					foreach($arg as $name){
						if(is_string($name)){
							if(array_key_exists($name, $this->_vars) && is_bool($this->_vars[$name])) $this->_vars[$name] = !$this->_vars[$name];
						} else {
							$this->add_history(sprintf("xor_vars cannot invert (XOR) var '%s'", $name), E_USER_WARNING);
						}
					}
				} else if(is_string($arg)){
					if(array_key_exists($arg, $this->_vars)) $this->_vars[$arg] = !$this->_vars[$arg];
				}
			}
			
			return true;
		} else {
			$this->throw_error(sprintf('Al least 1 argument (String or Array) must be given.', gettype($var)), E_USER_ERROR);
			return false;
		}
	}
	
}