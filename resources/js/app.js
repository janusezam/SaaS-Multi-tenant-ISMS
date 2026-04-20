import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const THEME_STORAGE_KEY = 'isms-theme';

const resolvePreferredTheme = () => {
	const tenantThemePreference = document.documentElement.getAttribute('data-tenant-theme-preference');

	try {
		const storedTheme = localStorage.getItem(THEME_STORAGE_KEY);

		if (storedTheme === 'light' || storedTheme === 'dark') {
			return storedTheme;
		}
	} catch (error) {
		// localStorage access can fail in strict browser modes
	}

	if (tenantThemePreference === 'light' || tenantThemePreference === 'dark') {
		return tenantThemePreference;
	}

	return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const setTheme = (theme) => {
	const nextTheme = theme === 'dark' ? 'dark' : 'light';

	document.documentElement.setAttribute('data-theme', nextTheme);
	document.documentElement.classList.toggle('dark', nextTheme === 'dark');

	try {
		localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
	} catch (error) {
		// Keep runtime theme even if persistence fails
	}

	const label = nextTheme === 'dark' ? 'Light mode' : 'Dark mode';
	document.querySelectorAll('[data-theme-label]').forEach((element) => {
		element.textContent = label;
	});
};

const initializeThemeToggle = () => {
	if (document.documentElement.hasAttribute('data-theme-static-auth')) {
		document.documentElement.setAttribute('data-theme', 'dark');
		document.documentElement.classList.add('dark');
		return;
	}

	if (document.documentElement.getAttribute('data-theme') === 'custom' || document.documentElement.getAttribute('data-theme-locked') === 'custom') {
		document.querySelectorAll('[data-theme-label]').forEach((element) => {
			element.textContent = 'Custom theme';
		});
		return;
	}

	setTheme(resolvePreferredTheme());

	document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
		button.addEventListener('click', () => {
			const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
			setTheme(currentTheme === 'dark' ? 'light' : 'dark');
		});
	});
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initializeThemeToggle);
} else {
	initializeThemeToggle();
}
