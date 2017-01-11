<?php
/**

Donnée initiale à la création de la classe :
--------------------------------------------
	- output_directory 		: .
	- output_name 				: -
	- output_file_name		: -
	- render_type 				: temporary
	- temporary_directory	: f0357x (nom généré par __construct; f0357x est la pour schématiser)
	- temporary_repository	: -
	- temporary_full_path	: f0357x (nom généré par __construct; f0357x est la pour schématiser)


Souhaité quelque soit le render_type : avec comme output_directory = Renders
--------------------------------------
	- Si temporary_repository undefined :
	
	
			FOLDER_NAME										FOLDER_IN_VARIABLE_NAME
	
	
		> Renders/f0357x/buffers					> output_directory/temporary_directory/buffers
		> Renders/f0357x/temps						> output_directory/temporary_directory/temps
		> Renders/f0357x/renders					> output_directory/temporary_directory/renders
		
		=> Renders										=> output_directory
		
		
	- Si temporary_repository défini et = Temps
	
		> Temps/f0357x/buffers						> temporary_repository/temporary_directory/buffers
		> Temps/f0357x/temps							> temporary_repository/temporary_directory/temps
		> Temps/f0357x/renders						> temporary_repository/temporary_directory/renders
		
		=> Renders										=> output_directory
		
		temporary_folders_path
		
		
Si render_type = temporary, ne rien faire
Si render_type = permanent, copier temporary_render_file dans output_directory

	
--- TEST DE LA CLASSE MODIFIEE ---
	
	>>> Test temp ss temp repo : OK
	>>> Test temp ac temp repo : OK
	>>> Test perm ss temp repo : OK
	>>> Test perm ac temp repo : OK

**/
?>