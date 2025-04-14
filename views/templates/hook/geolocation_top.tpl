{*<aside>
    <div class="alert alert-dark alert-dismissible fade show m-auto text-center" role="alert">
        {if isset($data_shop.url_shop) and isset($data_shop.iso_code)}
            {l s='Hemos detectado que tu pais es [1]%s[/1]' mod='shop_geolocation' tags=['<strong>'] sprintf=[$data_shop.country_name]}. {l s='Ir a tienda' mod='shop_geolocation'} <a href="{$data_shop.url_shop}">{$data_shop.country_name}</a>
            {else}
                {l s='Hemos detectado que tu pais es [1]%s[/1]' mod='shop_geolocation' tags=['<strong>'] sprintf=[$data_shop.country_name]}. {l s='No puedes comprar' mod='shop_geolocation'}
                {/if}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
    </div>
</aside>*}
{*<script type="text/javascript" src="https://cdn.rawgit.com/prashantchaudhary/ddslick/master/jquery.ddslick.min.js"></script>*}
<aside>
    <div id="geolocation-container" class="container-fluid p-0 m-0">
        <div id="geolocation-logo">
            <a href="{$urls.base_url}">
                <img class="logo img-fluid"
                     src="{$shop.logo}" {if isset($iqitTheme.rm_logo) && $iqitTheme.rm_logo != ''} srcset="{$iqitTheme.rm_logo} 2x"{/if}
                     alt="{$shop.name}">
            </a>
        </div>
        <div id="geolocation-alert" class="fade show m-0 p-0 text-left row" role="alert">
            <div class="container">
                <div class="selector-wrapper">
                    <div class="block-selector">
                        <p class="phrase_alert">{l s='Por favor, seleccione su tienda' mod='shop_geolocation'}:</p>
                        <div class="dropdown" id="dropdown_menu_flags">
                            <button class="btn dropdown-toggle button_selector_flag" type="button" autocomplete="off" data-toggle="dropdown"><img class="flags_img" src="{_MODULE_DIR_}shop_geolocation/views/img/flags/{$data_shop.iso_code|lower}.png"><span class="text_button">{$data_shop.country_name}</span></button>
                            <ul class="dropdown-menu list_flags_dropdown" data-live-search="true" autocomplete="off">
                                {foreach from=$all_related_stores item=country }
                                    <li class="flag-{$country.iso_code}" value="{$country.id_shop}"> <img class="flags_img" src="{_MODULE_DIR_}shop_geolocation/views/img/flags/{$country.iso_code|lower}.png" data-url="{$country.url_shop}" data-iso-code="{$country.iso_code|lower}" {if $country.iso_code|lower eq $data_shop.iso_code|lower }selected{/if}>
                                        {$country.name}
                                    </li>

                                {/foreach}
                            </ul>
                        </div>
                    </div>

                    <div class="stores_dropdown">
                        <div class="arrow">
                            {* <img src="{_MODULE_DIR_}shop_geolocation/views/img/flags/es.png">*}
                            <select id="storesDropdown" class="selectpicker" data-live-search="true" autocomplete="off">
                                {foreach from=$all_related_stores item=country }
                                <option data-thumbnail="{_MODULE_DIR_}shop_geolocation/views/img/flags/{$country.iso_code|lower}.png" data-url="{$country.url_shop}" data-iso-code="{$country.iso_code|lower}" {if $country.iso_code|lower eq $data_shop.iso_code|lower }selected{/if} value="{$country.id_shop}">
                                    {$country.name}
                                </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="submit_button">
                            <button type="submit" class="btn">{l s='Continuar' mod='shop_geolocation'}</button>
                    </div>
                    <div class="close_button">
                        <button type="button" class="close text-right" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"><img src="{_PS_THEME_URI_}assets/img/header-mb/close.svg"></span>
                            <span class="phrase_close"> {l s='Cerrar'}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>