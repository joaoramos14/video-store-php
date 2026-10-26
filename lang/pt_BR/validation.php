<?php

/*
| Mensagens de validação em português, só com as regras usadas no projeto.
| Se passar a usar outra regra, copie a chave de vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php.
*/

return [
    'between' => [
        'array'   => 'O campo :attribute deve ter entre :min e :max itens.',
        'file'    => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string'  => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'exists'   => 'O :attribute selecionado é inválido.',
    'integer'  => 'O campo :attribute deve ser um número inteiro.',
    'max'      => [
        'array'   => 'O campo :attribute não pode ter mais de :max itens.',
        'file'    => 'O campo :attribute não pode ser maior que :max kilobytes.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'string'  => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'min'      => [
        'array'   => 'O campo :attribute deve ter pelo menos :min itens.',
        'file'    => 'O campo :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string'  => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'numeric'  => 'O campo :attribute deve ser um número.',
    'required' => 'O campo :attribute é obrigatório.',
    'string'   => 'O campo :attribute deve ser um texto.',
    'unique'   => 'Este :attribute já está em uso.',
    'url'      => 'O campo :attribute deve ser uma URL válida.',

    'attributes' => [
        'days'        => 'dias',
        'rating'      => 'nota',
        'comment'     => 'comentário',
        'name'        => 'nome',
        'title'         => 'título',
        'director'      => 'diretor',
        'synopsis'      => 'sinopse',
        'year'          => 'ano',
        'duration'      => 'duração',
        'cover'         => 'capa',
        'stock'         => 'estoque',
        'daily_price'   => 'preço da diária',
        'price_tier_id' => 'faixa de preço',
        'genre_id'      => 'gênero',
        'tmdb_id'       => 'TMDB ID',
        'genre'         => 'gênero',
    ],
];
