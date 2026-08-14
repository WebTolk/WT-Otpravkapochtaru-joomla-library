# Поля Joomla Form

В пакете библиотеки `WT Otpravkapochtaru` есть поля Joomla Form для расширений. Они позволяют работать в интерфейсе Joomla с некоторыми типами данных, получаемых по API из вашего личного кабинета в сервисе `Отправка` Почты России.
Поля лежат в namespace `Webtolk\Otpravkapochtaru\Fields`.

На данный момент в библиотеке доступны 3 поля списков, которые отображают доступные в аккаунте ОПС, типы и категории отправлений с помощью метода `Otpravkapochtaru::otpravkaApi()->shippingPoints()`.

## XML-синтаксис

Укажите namespace поля через `addfieldprefix` на уровне `<fields>`, `<fieldset>` или на конкретном `<field>`.

```xml
<fields name="params" addfieldprefix="Webtolk\Otpravkapochtaru\Fields">
    <fieldset name="delivery">
        <field
            name="postoffice_code"
            type="opslist"
            label="COM_EXAMPLE_POSTOFFICE_CODE_LABEL"
        />
    </fieldset>
</fields>
```
Если у формы уже есть другой `addfieldprefix`, укажите prefix только у поля:

```xml
<field
    name="account_info"
    addfieldprefix="Webtolk\Otpravkapochtaru\Fields"
    type="accountinfo"
    label="COM_EXAMPLE_ACCOUNT_INFO_LABEL"
/>
```

## Доступные поля

| XML `type` | PHP-класс | Назначение |
| --- | --- | --- |
| `accountinfo` | `AccountinfoField` | Информационный блок о текущем аккаунте Почты России и лимитах API. Используется как `note`-поле в настройках. |
| `opslist` | `OpslistField` | Список ОПС, доступных аккаунту. Значение option равно `operator-postcode`, текст строится из индекса и адреса ОПС. |
| `mailtypes` | `MailtypesField` | Список типов отправлений для выбранного ОПС. Может работать как связанный список через AJAX. |
| `mailcategories` | `MailcategoriesField` | Список категорий отправлений для выбранного ОПС и типа отправления. Может работать как связанный список через AJAX. |

## Параметры XML

| Атрибут | Где используется | Значение и логика                                                                                                                                                                             |
| --- | --- |-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `name` | Все поля | Имя Joomla Form поля. Для связанных списков это имя также используется как ссылка в `requestfields`.                                                                                          |
| `type` | Все поля | Тип поля: `accountinfo`, `opslist`, `mailtypes` или `mailcategories`.                                                                                                                         |
| `addfieldprefix` | Все поля | Namespace `Webtolk\Otpravkapochtaru\Fields`, по которому Joomla находит классы полей. Можно задать на `<fields>` или на отдельном `<field>`.                                                  |
| `label` | Все поля | Языковая константа Joomla или хардкод (не рекомендуется).                                                                                                                                     |
| `description` | Все поля | Аналогично `label`                                                                                                                                                                            |
| `default` | Списковые поля | Значение поля по умолчанию. Для `mailtypes` и `mailcategories` текущее сохраненное значение сохраняется при серверном рендере, если зависимые значения еще не выбраны.                        |
| `class` | Списковые поля | Дополнительные CSS-классы. Для `mailtypes` и `mailcategories` класс `wt-linked-select-field` добавляется автоматически.                                                                       |
| `url` | `mailtypes`, `mailcategories` | URL AJAX-запроса для динамической перезагрузки списка. Скрипт добавляет к нему параметры из `requestfields` и CSRF token Joomla. В XML символ `&` нужно писать как `&amp;`.                   |
| `requestfields` | `mailtypes`, `mailcategories` | JSON-объект, где **ключ - имя параметра запроса** к AJAX endpoint, **значение - имя Joomla Form поля, откуда брать значение для отправки**. |

## Логика работы

- `opslist` показывает список ОПС, доступных в вашем аккаунте по договору с Почтой России. Если системный плагин отключен, учетные данные не заполнены или API недоступен, поле не ломает форму: вместо списка оно возвращает один option с диагностическим текстом.
- `mailtypes` читает значение параметра `postoffice_code` из формы. Если ОПС выбран, поле строит варианты по данным выбранного ОПС: сначала использует `user-available-mail-types`, а если этот список пустой, выводит уникальные `mail-type` из `user-available-products`. Подписи берутся из языковых констант `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_*`; если константа не найдена, показывается исходный код типа.
- `mailcategories` читает `postoffice_code` и `mail_type`. Оно фильтрует `user-available-products` выбранного ОПС по `mail-type` и выводит уникальные `mail-category`. Подписи берутся из языковых констант `PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_*`; если константа не найдена, показывается исходный код категории.

AJAX для связанных списков обслуживает системный плагин через `com_ajax` и action `getMailTypes` или `getMailCategories`. Скрипт поля ищет зависимые поля по `jform_{name}`, `jform_params_{name}`, точному `name`, `jform[params][name]` и по суффиксу `[name]`. Поэтому в `requestfields` обычно достаточно указывать короткие имена полей без `jform` и без `params`.

Если зависимое поле пустое, связанный список очищается и отключенный API-запрос не выполняется. При смене родительского поля дочерний список перезагружается, а затем генерирует событие `change`, чтобы обновить следующий уровень цепочки.

## Пример трёх связанных полей списков

Пример ниже показывает интерфейс из 3-х полей, где поле типов отправлений обновляется при изменении поля списка ОПС, а поле категорий отправлений обновляется при изменении обоих списков - и списка ОПС и списка типов отправлений.

```xml
<fields name="params" addfieldprefix="Webtolk\Otpravkapochtaru\Fields">
    <fieldset name="delivery">
        <field
            name="postoffice_code"
            type="opslist"
            label="PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_LABEL"
        />

        <field
            name="mail_type"
            type="mailtypes"
            label="PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_LABEL"
            url="index.php?option=com_ajax&amp;plugin=wtotpravkapochtaru&amp;group=system&amp;format=json&amp;action=getMailTypes"
            requestfields='{"postoffice_code":"postoffice_code"}'
        />

        <field
            name="mail_category"
            type="mailcategories"
            label="PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_LABEL"
            url="index.php?option=com_ajax&amp;plugin=wtotpravkapochtaru&amp;group=system&amp;format=json&amp;action=getMailCategories"
            requestfields='{"postoffice_code":"postoffice_code","mail_type":"mail_type"}'
        />
    </fieldset>
</fields>
```
