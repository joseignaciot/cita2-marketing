<?php

it('redirects root to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
