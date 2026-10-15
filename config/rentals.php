<?php

return [

    /*
    | Quantas horas o cliente tem para retirar o filme na loja depois de reservar.
    | Passado esse prazo, a reserva expira e a cópia volta para o estoque.
    */
    'reservation_hours' => (int) env('RENTAL_RESERVATION_HOURS', 24),

];
