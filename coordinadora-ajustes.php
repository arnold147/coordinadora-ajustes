<?php
/**
 * Plugin Name: Coordinadora Ajustes
 * Plugin URI: https://interactivos123.com
 * Description: Ajustes adicionales
 * Version: 1
 * Author: Arnold Salazar
 * License: GPL2
 *
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


//Crear Campo personalizado de 3dbin_field en el Checkout
add_action( 'woocommerce_before_order_notes', 'AECoordinadora_add_custom_checkout_field' );
  
function AECoordinadora_add_custom_checkout_field( $checkout ) { 
  
   woocommerce_form_field( '3dbin_field', array(        
      'type' => 'hidden',        
      'class' => array('form-row-wide my-hidden-field'),        
      'label' => '',        
      'placeholder' => '155555555',        
      'required' => true,        
      'default' => "Información del empaquetado",        
   ), $checkout->get_value( '3dbin_field' ) ); 
   
}




// Validar que el campo no este vacio
add_action( 'woocommerce_checkout_process', 'AECoordinadora_validate_new_checkout_field' ); 

function AECoordinadora_validate_new_checkout_field() {    
   if ( ! $_POST['3dbin_field'] ) {
      wc_add_notice( 'Debe completar el campo 3Dbin', 'error' );
   }
}




// Recibir datos y actulizar campo de 3dbin_field 
add_action( 'woocommerce_checkout_update_order_meta', 'AECoordinadora_save_new_checkout_field' );
  
function AECoordinadora_save_new_checkout_field( $order_id ) { 
    if ( $_POST['3dbin_field'] ) {		
	update_post_meta( $order_id, '_3dbin_field', esc_attr( $_POST['3dbin_field']));
	}
}


// Mostrar Campo 3Dbin en la pagina de pedidos 
add_action('woocommerce_admin_order_data_after_billing_address', 'AECoordinadora_show_field_3dbin', 10, 1 );
  
function AECoordinadora_show_field_3dbin( $order ) {    
   $order_id = $order->get_id(); 

	$dbin_field_meta = get_post_meta( $order_id, '_3dbin_field', true );
  
   if($dbin_field_meta) {	

	$cajadbin = html_entity_decode($dbin_field_meta);	
	$cajadbin = trim($cajadbin);
	
	
	//echo "<pre><h1 class='jsonenvio' style='display:none;'>$cajadbin</h1></pre>";
	$empaque_Html = empaque_hmtl($cajadbin);
	
	
	echo "<buttom class='button button-primary' id='3dbin_btn_ver'>Ver empaque</buttom>";    
	
	echo "<script>
	jQuery('document').ready(function() {
				
		jQuery(document).on('click', '#3dbin_btn_ver', function(){
			Swal.fire({
			  html: `$empaque_Html`,
		  showCloseButton: true,
		  width : '800px'
		  
		});
		});		
		
			
		
	});	
	</script>";  

   }else{
	  echo "Sin empaque"; 
   }
}


function empaque_hmtl($resp){

		
	
	$response = json_decode($resp,true);
	
	
	
	// mostrar errores
	if(isset($response['response']['errors'])){
		
		$respuesta = "";
		
		foreach($response['response']['errors'] as $error){ 
		  $respuesta .= $error['message'].'<br>';
		}
	  
		//return $respuesta;
	}
	

	// Mostrar los empaques
	if( $response['response']['status'] > -1 ){		
		
	//Obtener paquetes
	$b_packed = $response['response']['bins_packed'];
	
	$cantidadDeEmpaques = count($b_packed);
	 
	$respuesta .=  "<style>
		.tabla-binpakin td, .tabla-binpakin th {
			padding: 7px;
			font-size: 15px;
			border: 1px solid #efefef;
		}
		
		.tabla-binpakin{
			margin:0 auto 30px;
		}
		
		.tabla-binpakin th {
			color: #ffffff;
			font-weight: 500;
			background: #00d6ff;
			border-color: #00d6ff!important;
		}
		
		.azul-b{
			color:#00d6ff;
			margin-left:1em;
		}
		.medidas-cajafinal{
			margin-top: -15px;
			font-size: 15px;
		}
		
		</style>
	
		<h2>Los productos se han empacado en $cantidadDeEmpaques cajas</h2>";
	 
	  foreach ($b_packed as $bin){
		$anchoBin = $bin['bin_data']['w'];  
		$altoBin = $bin['bin_data']['h'];  
		$largoBin = $bin['bin_data']['d'];  
		$pesoBin = $bin['bin_data']['weight'];  
		  
		$pesoVolumen = ($anchoBin * $altoBin * $largoBin) / 2500; 		 
		 
		$respuesta .=  "
		
		<h4>Medidas de la caja: </h4>
		<p class='medidas-cajafinal'> 
		<b class='azul-b'>Ancho:</b> {$anchoBin}  
		<b class='azul-b'>Alto:</b> {$altoBin} 
		<b class='azul-b'>Largo:</b> {$largoBin}
		<b class='azul-b'>Volumen:</b> {$pesoVolumen} 
		<b class='azul-b'>Peso total:</b> {$pesoBin} kg
		</span></p>
		";
		  $items = $bin['items'];
		  
		  $respuesta .=  '<table class="tabla-binpakin">
			<tr><th>Nombre del producto</th>
				<th>Dimensiones</th>
				<th>Peso</th>
				<th>Ubicación en caja</th></tr>';
		  foreach ( $items as $item){ 				
			  
		  $respuesta .=  "<tr> 
					<td>{$item['id']}</td>       
					<td> Ancho: {$item['w']} <br> Alto: {$item['h']} <br> Largo: {$item['d']}</td>
					<td>{$item['wg']} kg</td>       
					<td><img src=\"{$item['image_sbs']}\"></td>
				</tr>";
		  }   
		  $respuesta .=  '</table>';
		 $respuesta .=  '<hr>';
	 
	  }//fin foreach
	  
	  return $respuesta;
	}//fin if
}


function add_checkout_script() { ?>

    <script type="text/javascript">  

        jQuery(document).on( "ready", function(){	
		
			
			<?php //Funcion para sacar una cookie del string por el nombre de la misma ?>
			function getCookie(cname) {
			  var name = cname + "=";
			  var decodedCookie = decodeURIComponent(document.cookie);
			  var ca = decodedCookie.split(';');
			  for(var i = 0; i <ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
				  c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
				  return c.substring(name.length, c.length);
				}
			  }
			  return false; 
			}
			
			<?php // Funcion para rellenar el campo de codigo postal con la cookie de woocommerce ?>
			function colocarCookieEnCampoCodigoPostal(){
				
				let cookieWoocommercePaking = getCookie('woocommerce_cart_hash');
				
				jQuery("#shipping_postcode").val(cookieWoocommercePaking).hide();
				jQuery("#billing_postcode").val(cookieWoocommercePaking).hide();
			}
			
			colocarCookieEnCampoCodigoPostal();
			
			
				
			<?php // Actualizar el ajax del checkout al cargar la pagina	?>
			setTimeout(function(){jQuery(document.body).trigger("update_checkout");}, 3000);
			

			
			jQuery('.woocommerce-checkout').on('click', function(){				
				
				let codigopostalcookie = jQuery("#shipping_postcode").val();
				let cookieWoocomerce = getCookie('woocommerce_cart_hash');
				
				<?php // Valida si la cookie del codigopostal y el navegador son las mismas ?>
				if(codigopostalcookie != cookieWoocomerce){
					jQuery(document.body).trigger("update_checkout");
				}
				console.log(cookieWoocomerce);
				colocarCookieEnCampoCodigoPostal();				
				
			});

			
		}); //jquery readdy	

    </script>

<?php       
}
add_action( 'woocommerce_after_checkout_form', 'add_checkout_script' );





 
 