<?php

test('seed tenant demo command fails for unknown tenant', function () {
    $this->artisan('isms:seed-tenant-demo missing-tenant.isms.test')
        ->expectsOutputToContain('Tenant not found for input: missing-tenant.isms.test')
        ->assertExitCode(1);
});
