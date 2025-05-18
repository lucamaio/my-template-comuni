<?php
/**
 * Nav Menu API: Footer_Menu_Walker class
 *
 * @package WordPress
 * @subpackage Nav_Menus
 * @since 4.6.0
 */

/**
 * Custom class used to implement an HTML list of nav menu items.
 *
 * @since 3.0.0
 *
 * @see Walker
 */


class Footer_Menu_Walker extends Walker_Nav_Menu {
	function start_el(&$output, $item, $depth=0, $args=[], $id=0) {
		$output .= "<li>";
		if ( !$item->url ) $item['url'] = '#';
		
		
	       // Sovrascrivi l'URL per "Luoghi" se è vuoto
	        if ($item->title == 'Luoghi' && $item->url == '/vivere-il-comune') {
	            $item->url = $item->url . '/luoghi';
	        }
	
	        // Sovrascrivi l'URL per "Eventi" se è vuoto
	        if ($item->title == 'Eventi' && $item->url == '/vivere-il-comune') {
	            $item->url = $item->url . '/eventi';
	        }

               // Sovrascrivi l'URL per "Avvisi" se è vuoto
	        if ($item->title == 'Avvisi' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/avvisi';
	        }
	
               // Sovrascrivi l'URL per "Comunicati" se è vuoto
	        if ($item->title == 'Comunicati' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/comunicati';
	        }

               // Sovrascrivi l'URL per "Notizie" se è vuoto
	        if ($item->title == 'Notizie' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/notizie';
	        }
		
               // Sovrascrivi l'URL per "Notizie" se è vuoto
	        if ($item->title == 'Privacy' && $item->url == '/privacy') {
	            $item->url = '/page-templates/privacy';
	        }

		// Sovrascrivi l'URL per "Prenota Appuntamento" se è vuoto
		 if ($item->title == 'Prenota appuntamento' && $item->url == '/prenotazioni') {
                   $item->url = '/page-templates/prenotazioni';
		}

		
		// Sovrascrivi l'URL per "Segnalazione disservizio" se è vuoto
		if ($item->title == 'Segnalazione disservizio' && $item->url == '#') {
		    $item->url = 'mailto:' . dci_get_option("email_principale");
		}
		
		// Sovrascrivi l'URL per "Amministrazione trasparente" se è vuoto
		if ($item->title == 'Amministrazione trasparente' && $item->url == '#') {
		   $item->url =  esc_url(dci_get_option("link_ammtrasparente")) . '" target="_blank" ';
		}

		// Sovrascrivi l'URL per "Informativa privacy" se è vuoto
		if ($item->title == 'Informativa privacy' && $item->url == '#') {
		   $item->url = '/page-templates/privacy';
		}
		
		// Sovrascrivi l'URL per "Dichiarazione di accessibilità" se è vuoto richiama il link sul pannello Admin solo se compilato
		$accessibilita_url = dci_get_option("dichiarazioneaccessibilita");		
		if ($item->title == 'Dichiarazione di accessibilità' && $item->url == '#' && !empty($accessibilita_url)) {
		    $item->url = esc_url(dci_get_option("dichiarazioneaccessibilita")) . '" target="_blank" ';
		}
		
		// Sovrascrivi l'URL per "note-legali" se è vuoto
		if ($item->title == 'Note legali' && $item->url == '#') {
		   $item->url = '/page-templates/note-legali';
		}		

		
		$data_element = '';
		   // Imposta data-elements
		        if ($item->title == 'Leggi le FAQ') {
		            $data_element = "data-element='faq'";
		        }
		        if ($item->title == 'Segnalazione disservizio') {
		            $data_element = "data-element='report-inefficiency'";
		        }
		        if ($item->title == 'Informativa privacy') {
		            $data_element = "data-element='privacy-policy-link'";
		        }
		        if ($item->title == 'Dichiarazione di accessibilità') {
		            $data_element = "data-element='accessibility-link'";
		        }
		        if ($item->title == 'Note legali') {
		            $data_element = "data-element='legal-notes'";
		        }
		


		$output .= '<a href="' . $item->url . '" '.$data_element.'>';
		$output .= $item->title;		
		$output .= '</a>';
	}
}
