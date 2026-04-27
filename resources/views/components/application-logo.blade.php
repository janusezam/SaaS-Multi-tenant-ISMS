@php
	$logoUrl = asset('images/isms-logo.png');

	if (tenant() !== null) {
		$tenantSetting = \App\Models\TenantSetting::query()->firstWhere('tenant_id', (string) tenant('id'));
		$logoPath = trim((string) ($tenantSetting?->branding_logo_path ?? ''));

		if ($logoPath !== '') {
			if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
				$logoUrl = $logoPath;
			} else {
				$normalizedPath = ltrim(str_replace('\\', '/', $logoPath), '/');
				$normalizedPath = preg_replace('#^(public/)+#', '', $normalizedPath) ?? $normalizedPath;
				$normalizedPath = preg_replace('#^(storage/)+#', '', $normalizedPath) ?? $normalizedPath;
				$logoUrl = tenant_asset($normalizedPath);
			}
		}
	}
@endphp

<img src="{{ $logoUrl }}" alt="ISMS logo" {{ $attributes }}>
