<div>
    <div wire:poll.500ms="getData"></div>

    <script>
        window.addEventListener('getData', event => {
            @this.ship_date = $('#ship_date').val();
            @this.shipping_instruction = $('#shipping_instruction').val();
            @this.shipping_address_id = $('#shipping_address_id').val();
            @this.ship_to_name = $('#ship_to_name').val();
            @this.ship_to_address1 = $('#ship_to_address1').val();
            @this.ship_to_address2 = $('#ship_to_address2').val();
            @this.ship_to_address3 = $('#ship_to_address3').val();
            @this.postal_code = $('#postal_code').val();
        });
    </script>
</div>
