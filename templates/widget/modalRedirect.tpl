<div class="modal fade" id="modalRedirect" tabindex="-1" role="dialog" aria-labelledby="modalRedirectLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-body text-center">
            {l s="We’ve noticed you are navigating from our %1s website, would you like to be redirected to %2s?" sprintf=[$shop, $shop_redirec] mod='shop_geolocation'} 
        </div>
        <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-primary">
                {l s='Yes please take me there!' mod='shop_geolocation'}
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                {l s='Stay on %1s' sprintf=[$shop] mod='shop_geolocation'}
            </button>
        </div>
        </div>
    </div>
</div>