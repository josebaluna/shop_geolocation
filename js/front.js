/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

// VARIABLES GLOBALES


// EVENTOS
$(document).ready(function(){
  //popup
  if (!(document.cookie.includes("id_shop_geolocation",0))) {
      $('#modalRedirect').modal('show');
  }
  // redirección a tienda seleccionada
  $(document).on('click','#geolocation-container button:submit', function (e) {
     var shop = $("#storesDropdown option:selected").val();
     setCookie("id_shop_geolocation", shop, 1);
     redirectToSelectedShop();
  });
  
  //capturar X cerrar alert
  $(document).on('close.bs.alert', '#geolocation-alert', function () {
      $('#geolocation-container').remove();
      var shop = $("#storesDropdown option:selected").val();
      setCookie("id_shop_geolocation", shop, 1);
  });
});

// FUNCIONES
function redirectToSelectedShop(){
  var url = $("#storesDropdown option:selected").data('url');

  window.location.href = url;
}

function setCookie(cname, cvalue, exdays) {
      var d = new Date();
      d.setTime(d.getTime() + (exdays *24*60*60 * 1000));
      var expires = "expires=" + d.toUTCString();
      document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

$(document).on('click', '#modalRedirect button.btn-primary', function () {
  let shop = $('.shop-not-selected').attr('id_shop');
  setCookie("id_shop_geolocation", shop, 1);
  window.location.assign($('.shop-not-selected').attr('href'));
})

$(document).on('click', '#modalRedirect button.btn-secondary', function () {
  let shop = $('.shop-not-selected').attr('id_shop');
  setCookie("id_shop_geolocation", shop, 1);
})