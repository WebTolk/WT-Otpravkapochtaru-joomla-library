(() => {
    'use strict';

    if (window.WtOtpravkapochtaruLinkedSelectFieldsLoaded === true) {
        return;
    }

    window.WtOtpravkapochtaruLinkedSelectFieldsLoaded = true;

    const FIELD_SELECTOR = '.wt-linked-select-field[data-wt-url]';
    const EMPTY_OPTION_TEXT = '';

    const init = () => {
        const fields = Array.from(document.querySelectorAll(FIELD_SELECTOR));

        if (fields.length === 0) {
            return;
        }

        const csrfTokenName = detectCsrfTokenName();
        const tokenPairs = csrfTokenName === '' ? {} : { [csrfTokenName]: 1 };

        fields.forEach((field) => {
            const config = readFieldConfig(field);
            const dependencyFieldNames = getUniqueDependencyFieldNames(config.requestfields);

            dependencyFieldNames.forEach((fieldName) => {
                const dependencyField = resolveFieldByName(fieldName);

                if (dependencyField !== null) {
                    dependencyField.addEventListener('change', () => {
                        void refreshField(field, tokenPairs);
                    });
                }
            });

            if (dependencyFieldNames.length === 0) {
                setEmptyOption(field, EMPTY_OPTION_TEXT);
                return;
            }

            void refreshField(field, tokenPairs);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function refreshField(field, tokenPairs) {
        const config = readFieldConfig(field);
        const queryEntries = buildQueryEntries(config.requestfields);

        if (!queryEntries.ready) {
            cancelFieldFetch(field);
            setEmptyOption(field, EMPTY_OPTION_TEXT);
            dispatchLinkedChange(field);
            return Promise.resolve(false);
        }

        const query = new URLSearchParams(queryEntries.query);

        if (Object.keys(tokenPairs).length > 0) {
            Object.entries(tokenPairs).forEach(([name, value]) => {
                query.append(name, String(value));
            });
        }

        const requestUrl = buildRequestUrl(config.url, query);
        const requestId = beginFieldFetch(field);

        return fetch(requestUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json()
                        .catch(() => ({ success: false }))
                        .then((payload) => {
                            const errorMessage = parseErrorMessage(payload);
                            throw new Error(errorMessage || 'Error');
                        });
                }

                return response.json();
            })
            .then((payload) => {
                if (typeof payload !== 'object' || payload === null) {
                    throw new Error('Invalid payload');
                }

                const data = Array.isArray(payload.data) ? payload.data[0] : payload.data;

                if (payload.success !== true || !data || !Array.isArray(data.options)) {
                    const errorMessage = parseErrorMessage(payload);
                    throw new Error(errorMessage || 'Invalid payload');
                }

                if (!isCurrentFieldFetch(field, requestId)) {
                    return false;
                }

                const previousValue = field.value;
                const options = data.options;

                const values = options
                    .filter((option) =>
                        option !== null
                        && typeof option === 'object'
                        && typeof option.value === 'string'
                        && option.value !== '')
                    .map((option) => ({
                        value: option.value,
                        text: typeof option.text === 'string' ? option.text : option.value,
                    }));

                updateSelectOptions(field, values);

                if (previousValue !== '' && field.querySelector(`option[value="${cssEscape(previousValue)}"]`) !== null) {
                    field.value = previousValue;
                }

                dispatchLinkedChange(field);

                return true;
            })
            .catch(() => {
                if (isCurrentFieldFetch(field, requestId)) {
                    setEmptyOption(field, EMPTY_OPTION_TEXT);
                    dispatchLinkedChange(field);
                }
            })
            .finally(() => {
                finishFieldFetch(field, requestId);
            });
    }

    function buildQueryEntries(requestfields) {
        const queryEntries = {};

        if (Object.keys(requestfields).length === 0) {
            return {
                ready: false,
                query: queryEntries,
            };
        }

        const requiredEntries = Object.entries(requestfields);

        for (let i = 0; i < requiredEntries.length; i += 1) {
            const [paramName, fieldName] = requiredEntries[i];
            const dependencyField = resolveFieldByName(fieldName);
            const requestValue = normalizeInputValue(dependencyField);

            if (requestValue === '') {
                return {
                    ready: false,
                    query: queryEntries,
                };
            }

            queryEntries[paramName] = requestValue;
        }

        return {
            ready: true,
            query: queryEntries,
        };
    }

    function getUniqueDependencyFieldNames(requestfields) {
        return Array.from(new Set(Object.values(requestfields || {})));
    }

    function beginFieldFetch(field) {
        if (field.dataset.wtLoading !== '1') {
            field.dataset.wtDisabledBeforeFetch = field.disabled ? '1' : '0';
        }

        const requestId = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        field.dataset.wtLoading = '1';
        field.dataset.wtRequestId = requestId;
        field.disabled = true;

        return requestId;
    }

    function finishFieldFetch(field, requestId) {
        if (!isCurrentFieldFetch(field, requestId)) {
            return;
        }

        field.disabled = field.dataset.wtDisabledBeforeFetch === '1';
        delete field.dataset.wtLoading;
        delete field.dataset.wtRequestId;
        delete field.dataset.wtDisabledBeforeFetch;
    }

    function cancelFieldFetch(field) {
        if (field.dataset.wtLoading !== '1') {
            return;
        }

        field.disabled = field.dataset.wtDisabledBeforeFetch === '1';
        delete field.dataset.wtLoading;
        delete field.dataset.wtRequestId;
        delete field.dataset.wtDisabledBeforeFetch;
    }

    function isCurrentFieldFetch(field, requestId) {
        return field.dataset.wtLoading === '1' && field.dataset.wtRequestId === requestId;
    }

    function setEmptyOption(field, text) {
        const select = field;
        clearOptions(select);

        const option = new Option(text || '', '');
        option.disabled = true;
        option.selected = true;

        select.appendChild(option);
    }

    function updateSelectOptions(field, values) {
        const select = field;
        const selectedValue = select.value;

        clearOptions(select);

        if (values.length === 0) {
            const option = new Option('', '');
            option.disabled = true;
            option.selected = true;
            select.appendChild(option);
            return;
        }

        values.forEach((item) => {
            const option = new Option(item.text, item.value);
            select.add(option);
        });

        if (selectedValue !== '' && select.querySelector(`option[value="${cssEscape(selectedValue)}"]`) !== null) {
            select.value = selectedValue;
        }
    }

    function clearOptions(select) {
        while (select.options.length > 0) {
            select.options[0].remove();
        }
    }

    function dispatchLinkedChange(field) {
        const changeEvent = new Event('change', { bubbles: true });
        field.dispatchEvent(changeEvent);
    }

    function parseErrorMessage(payload) {
        if (payload !== null && typeof payload === 'object' && typeof payload.message === 'string') {
            return payload.message;
        }

        return '';
    }

    function readFieldConfig(field) {
        return {
            url: field.dataset.wtUrl || '',
            requestfields: readRequestFields(field.dataset.wtRequestfields || ''),
        };
    }

    function readRequestFields(rawValue) {
        if (rawValue !== '') {
            try {
                const requestfields = JSON.parse(rawValue);

                if (requestfields !== null
                    && typeof requestfields === 'object'
                    && !Array.isArray(requestfields)
                ) {
                    const validEntries = Object.entries(requestfields).filter(
                        ([requestField, formField]) => typeof requestField === 'string'
                            && requestField !== ''
                            && typeof formField === 'string'
                            && formField !== ''
                    );

                    if (validEntries.length > 0) {
                        const resolvedRequestFields = {};

                        validEntries.forEach(([requestField, formField]) => {
                            resolvedRequestFields[requestField] = formField;
                        });

                        return resolvedRequestFields;
                    }
                }
            } catch (error) {
                // no-op
            }
        }

        return {};
    }

    function resolveFieldByName(name) {
        if (!name) {
            return null;
        }

        const withId = document.getElementById(`jform_${cssEscape(name)}`);
        if (withId !== null) {
            return withId;
        }

        const exact = document.querySelector(`[name="${cssEscape(name)}"]`);
        if (exact !== null) {
            return exact;
        }

        const suffixCandidates = Array.from(document.querySelectorAll('select, input, textarea'))
            .filter((element) => element.name !== undefined && element.name !== null && element.name.endsWith(`[${name}]`));

        return suffixCandidates[0] || null;
    }

    function buildRequestUrl(rawUrl, params) {
        const url = new URL(rawUrl, window.location.href);
        params.forEach((value, key) => {
            url.searchParams.set(key, value);
        });

        return url.toString();
    }

    function normalizeInputValue(field) {
        if (!field) {
            return '';
        }

        return String(field.value || '').trim();
    }

    function detectCsrfTokenName() {
        if (window.Joomla && typeof window.Joomla.getOptions === 'function') {
            const optionTokenName = window.Joomla.getOptions('csrf.token');

            if (typeof optionTokenName === 'string' && /^[a-z0-9]{16,}$/i.test(optionTokenName)) {
                return optionTokenName;
            }
        }

        const hiddenFields = Array.from(document.querySelectorAll('input[type="hidden"]'));

        const tokenField = hiddenFields.find((input) => {
            if (input === null || input.name === undefined || input.name === '') {
                return false;
            }

            return input.value === '1' && /^[a-z0-9]{16,}$/i.test(input.name);
        });

        return tokenField === undefined ? '' : tokenField.name;
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }

        return String(value).replace(/["\\]/g, '\\$&');
    }
})();
