document.addEventListener('DOMContentLoaded', () => {
    const dropdowns = [...document.querySelectorAll('[data-dispatcher-dropdown]')];

    function closeDropdown(dropdown, restoreFocus = false) {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('.dispatcher-dropdown-menu');
        dropdown.classList.remove('is-open', 'is-open-upward');
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        dropdown.querySelectorAll('[data-dropdown-option]').forEach((option) => {
            option.tabIndex = -1;
        });
        if (restoreFocus) trigger.focus();
    }

    function openDropdown(dropdown, focusSelected = false, moveFocus = true) {
        dropdowns.forEach((other) => {
            if (other !== dropdown) closeDropdown(other);
        });

        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('.dispatcher-dropdown-menu');
        const options = visibleOptions(dropdown);
        if (trigger.disabled || options.length === 0) return;

        dropdown.classList.add('is-open');
        menu.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        positionMenu(dropdown, menu);
        options.forEach((option) => { option.tabIndex = -1; });

        const activeOption = focusSelected
            ? options.find((option) => option.getAttribute('aria-selected') === 'true') || options[0]
            : options[0];
        if (moveFocus) {
            setActiveOption(dropdown, activeOption);
        }
    }

    function positionMenu(dropdown, menu) {
        dropdown.classList.remove('is-open-upward');
        const bounds = dropdown.getBoundingClientRect();
        const spaceBelow = window.innerHeight - bounds.bottom;
        const spaceAbove = bounds.top;
        const opensUpward = spaceBelow < Math.min(menu.scrollHeight, 280) && spaceAbove > spaceBelow;
        const availableSpace = opensUpward ? spaceAbove : spaceBelow;
        const maxHeight = Math.max(0, Math.min(280, window.innerHeight * 0.42, availableSpace - 16));
        menu.style.maxHeight = `${maxHeight}px`;
        if (opensUpward) {
            dropdown.classList.add('is-open-upward');
        }
    }

    function visibleOptions(dropdown) {
        return [...dropdown.querySelectorAll('[data-dropdown-option]')]
            .filter((option) => !option.hidden);
    }

    function setActiveOption(dropdown, option) {
        if (!option) return;
        dropdown.querySelectorAll('[data-dropdown-option]').forEach((item) => {
            item.classList.remove('is-active');
            item.tabIndex = -1;
        });
        option.classList.add('is-active');
        option.tabIndex = 0;
        option.focus();
        option.scrollIntoView({ block: 'nearest' });
    }

    function selectOption(dropdown, option) {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const valueInput = dropdown.querySelector('[data-dropdown-value]');
        const value = option.dataset.value;
        const label = option.dataset.label;

        valueInput.value = value;
        if (trigger.matches('input')) trigger.value = label;
        else dropdown.querySelector('[data-dropdown-label]').textContent = label;

        dropdown.querySelectorAll('[data-dropdown-option]').forEach((item) => {
            item.setAttribute('aria-selected', String(item === option));
            item.classList.remove('is-active');
        });
        valueInput.dispatchEvent(new Event('input', { bubbles: true }));
        valueInput.dispatchEvent(new Event('change', { bubbles: true }));
        closeDropdown(dropdown, true);
    }

    function filterOptions(dropdown, query) {
        const normalizedQuery = query.trim().toLocaleLowerCase();
        let matches = 0;
        dropdown.querySelectorAll('[data-dropdown-option]').forEach((option) => {
            option.hidden = !option.dataset.label.toLocaleLowerCase().includes(normalizedQuery);
            if (!option.hidden) matches++;
        });
        dropdown.querySelector('[data-dropdown-empty]').hidden = matches > 0;
        return visibleOptions(dropdown);
    }

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const valueInput = dropdown.querySelector('[data-dropdown-value]');
        const searchable = dropdown.dataset.searchable === 'true';
        const menu = dropdown.querySelector('.dispatcher-dropdown-menu');
        const options = dropdown.querySelectorAll('[data-dropdown-option]');

        options.forEach((option) => {
            option.tabIndex = -1;
            option.addEventListener('click', () => selectOption(dropdown, option));
            option.addEventListener('keydown', (event) => {
                const visible = visibleOptions(dropdown);
                const index = visible.indexOf(option);
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const direction = event.key === 'ArrowDown' ? 1 : -1;
                    setActiveOption(dropdown, visible[(index + direction + visible.length) % visible.length]);
                } else if (event.key === 'Home' || event.key === 'End') {
                    event.preventDefault();
                    setActiveOption(dropdown, event.key === 'Home' ? visible[0] : visible.at(-1));
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeDropdown(dropdown, true);
                }
            });
        });

        trigger.addEventListener('click', () => {
            if (searchable) {
                if (menu.hidden) openDropdown(dropdown, true, false);
            } else if (menu.hidden) {
                openDropdown(dropdown, true, false);
            } else {
                closeDropdown(dropdown);
            }
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !menu.hidden) {
                event.preventDefault();
                closeDropdown(dropdown, true);
            } else if (searchable && event.key === 'Enter' && !menu.hidden) {
                const activeOption = dropdown.querySelector('[data-dropdown-option].is-active');
                if (activeOption) {
                    event.preventDefault();
                    selectOption(dropdown, activeOption);
                }
            } else if (searchable && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                const visible = visibleOptions(dropdown);
                if (menu.hidden) {
                    openDropdown(dropdown, event.key === 'ArrowUp');
                } else {
                    setActiveOption(
                        dropdown,
                        event.key === 'ArrowDown' ? visible[0] : visible[visible.length - 1]
                    );
                }
            } else if (!searchable && ['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                if (menu.hidden) {
                    openDropdown(dropdown, event.key === 'ArrowUp');
                }
            }
        });

        if (searchable) {
            trigger.addEventListener('input', () => {
                valueInput.value = trigger.value;
                if (menu.hidden) openDropdown(dropdown, false, false);
                const matches = filterOptions(dropdown, trigger.value);
                dropdown.querySelectorAll('[data-dropdown-option]').forEach((option) => {
                    option.classList.remove('is-active');
                });
                if (matches.length) matches[0].classList.add('is-active');
            });
        }
    });

    document.addEventListener('click', (event) => {
        dropdowns.forEach((dropdown) => {
            if (!dropdown.contains(event.target) && dropdown.classList.contains('is-open')) {
                closeDropdown(dropdown);
            }
        });
    });

    document.addEventListener('reset', (event) => {
        window.setTimeout(() => {
            dropdowns.forEach((dropdown) => {
                if (!event.target.contains(dropdown)) return;
                const value = dropdown.querySelector('[data-dropdown-value]').value;
                const selected = [...dropdown.querySelectorAll('[data-dropdown-option]')]
                    .find((option) => option.dataset.value === value);
                dropdown.querySelectorAll('[data-dropdown-option]').forEach((option) => {
                    option.hidden = false;
                    option.setAttribute('aria-selected', String(option === selected));
                });
                dropdown.querySelector('[data-dropdown-empty]').hidden = true;
                const trigger = dropdown.querySelector('[data-dropdown-trigger]');
                if (trigger.matches('input')) {
                    trigger.value = selected?.dataset.label || value;
                } else {
                    dropdown.querySelector('[data-dropdown-label]').textContent =
                        selected?.dataset.label || dropdown.dataset.placeholder;
                }
                closeDropdown(dropdown);
            });
        });
    });
});
