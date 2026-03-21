<?php

it('returns not found for central root path', function () {
    $response = $this->get('/');

    $response->assertStatus(404);
});
