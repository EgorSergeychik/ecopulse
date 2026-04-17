<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Поле :attribute має бути прийнятим.',
    'accepted_if' => 'Поле :attribute має бути прийнятим, коли :other дорівнює :value.',
    'active_url' => 'Поле :attribute має бути дійсним URL.',
    'after' => 'Поле :attribute має бути датою після :date.',
    'after_or_equal' => 'Поле :attribute має бути датою після або рівною :date.',
    'alpha' => 'Поле :attribute може містити лише літери.',
    'alpha_dash' => 'Поле :attribute може містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num' => 'Поле :attribute може містити лише літери та цифри.',
    'any_of' => 'Поле :attribute є недійсним.',
    'array' => 'Поле :attribute має бути масивом.',
    'ascii' => 'Поле :attribute може містити лише однобайтові буквено-цифрові символи та символи.',
    'before' => 'Поле :attribute має бути датою до :date.',
    'before_or_equal' => 'Поле :attribute має бути датою до або рівною :date.',
    'between' => [
        'array' => 'Поле :attribute має містити від :min до :max елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути від :min до :max кілобайт.',
        'numeric' => 'Значення поля :attribute має бути від :min до :max.',
        'string' => 'Довжина рядка в полі :attribute має бути від :min до :max символів.',
    ],
    'boolean' => 'Поле :attribute має бути true або false.',
    'can' => 'Поле :attribute містить неавторизоване значення.',
    'confirmed' => 'Підтвердження поля :attribute не збігається.',
    'contains' => 'Поле :attribute не містить обовʼязкового значення.',
    'current_password' => 'Пароль є неправильним.',
    'date' => 'Поле :attribute має бути дійсною датою.',
    'date_equals' => 'Поле :attribute має бути датою, що дорівнює :date.',
    'date_format' => 'Поле :attribute має відповідати формату :format.',
    'decimal' => 'Поле :attribute має містити :decimal знаків після коми.',
    'declined' => 'Поле :attribute має бути відхиленим.',
    'declined_if' => 'Поле :attribute має бути відхиленим, коли :other дорівнює :value.',
    'different' => 'Поля :attribute та :other мають бути різними.',
    'digits' => 'Поле :attribute має містити :digits цифр.',
    'digits_between' => 'Кількість цифр в полі :attribute має бути від :min до :max.',
    'dimensions' => 'Поле :attribute має недійсні розміри зображення.',
    'distinct' => 'Поле :attribute містить дубльоване значення.',
    'doesnt_contain' => 'Поле :attribute не може містити жодне з наступних значень: :values.',
    'doesnt_end_with' => 'Поле :attribute не може закінчуватися одним із наступних значень: :values.',
    'doesnt_start_with' => 'Поле :attribute не може починатися одним із наступних значень: :values.',
    'email' => 'Поле :attribute має бути дійсною електронною адресою.',
    'encoding' => 'Поле :attribute має бути закодоване у форматі :encoding.',
    'ends_with' => 'Поле :attribute має закінчуватися одним із наступних значень: :values.',
    'enum' => 'Вибране значення для :attribute є недійсним.',
    'exists' => 'Вибране значення для :attribute є недійсним.',
    'extensions' => 'Поле :attribute має мати одне з наступних розширень: :values.',
    'file' => 'Поле :attribute має бути файлом.',
    'filled' => 'Поле :attribute повинно мати значення.',
    'gt' => [
        'array' => 'Поле :attribute має містити більше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути більшим за :value кілобайт.',
        'numeric' => 'Значення поля :attribute має бути більшим за :value.',
        'string' => 'Довжина рядка в полі :attribute має бути більшою за :value символів.',
    ],
    'gte' => [
        'array' => 'Поле :attribute має містити :value або більше елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути більшим або рівним :value кілобайт.',
        'numeric' => 'Значення поля :attribute має бути більшим або рівним :value.',
        'string' => 'Довжина рядка в полі :attribute має бути більшою або рівною :value символів.',
    ],
    'hex_color' => 'Поле :attribute має бути дійсним шістнадцятковим кольором.',
    'image' => 'Поле :attribute має бути зображенням.',
    'in' => 'Вибране значення для :attribute є недійсним.',
    'in_array' => 'Значення поля :attribute має існувати в :other.',
    'in_array_keys' => 'Поле :attribute має містити принаймні один з наступних ключів: :values.',
    'integer' => 'Поле :attribute має бути цілим числом.',
    'ip' => 'Поле :attribute має бути дійсною IP-адресою.',
    'ipv4' => 'Поле :attribute має бути дійсною IPv4-адресою.',
    'ipv6' => 'Поле :attribute має бути дійсною IPv6-адресою.',
    'json' => 'Поле :attribute має бути дійсним JSON-рядком.',
    'list' => 'Поле :attribute має бути списком.',
    'lowercase' => 'Поле :attribute має бути у нижньому регістрі.',
    'lt' => [
        'array' => 'Поле :attribute має містити менше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути меншим за :value кілобайт.',
        'numeric' => 'Значення поля :attribute має бути меншим за :value.',
        'string' => 'Довжина рядка в полі :attribute має бути меншою за :value символів.',
    ],
    'lte' => [
        'array' => 'Поле :attribute не може містити більше :value елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути меншим або рівним :value кілобайт.',
        'numeric' => 'Значення поля :attribute має бути меншим або рівним :value.',
        'string' => 'Довжина рядка в полі :attribute має бути меншою або рівною :value символів.',
    ],
    'mac_address' => 'Поле :attribute має бути дійсною MAC-адресою.',
    'max' => [
        'array' => 'Поле :attribute не може містити більше :max елементів.',
        'file' => 'Розмір файлу в полі :attribute не може перевищувати :max кілобайт.',
        'numeric' => 'Значення поля :attribute не може бути більшим за :max.',
        'string' => 'Довжина рядка в полі :attribute не може перевищувати :max символів.',
    ],
    'max_digits' => 'Поле :attribute не може містити більше :max цифр.',
    'mimes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'mimetypes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'min' => [
        'array' => 'Поле :attribute має містити принаймні :min елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути принаймні :min кілобайт.',
        'numeric' => 'Значення поля :attribute має бути принаймні :min.',
        'string' => 'Довжина рядка в полі :attribute має бути принаймні :min символів.',
    ],
    'min_digits' => 'Поле :attribute має містити принаймні :min цифр.',
    'missing' => 'Поле :attribute має бути відсутнім.',
    'missing_if' => 'Поле :attribute має бути відсутнім, коли :other дорівнює :value.',
    'missing_unless' => 'Поле :attribute має бути відсутнім, крім випадку коли :other дорівнює :value.',
    'missing_with' => 'Поле :attribute має бути відсутнім, коли присутнє :values.',
    'missing_with_all' => 'Поле :attribute має бути відсутнім, коли присутні всі :values.',
    'multiple_of' => 'Поле :attribute має бути кратним :value.',
    'not_in' => 'Вибране значення для :attribute є недійсним.',
    'not_regex' => 'Формат поля :attribute є недійсним.',
    'numeric' => 'Поле :attribute має бути числом.',
    'password' => [
        'letters' => 'Поле :attribute має містити принаймні одну літеру.',
        'mixed' => 'Поле :attribute має містити принаймні одну велику та одну малу літеру.',
        'numbers' => 'Поле :attribute має містити принаймні одну цифру.',
        'symbols' => 'Поле :attribute має містити принаймні один символ.',
        'uncompromised' => 'Введене :attribute було виявлене в витіку даних. Будь ласка, виберіть інше :attribute.',
    ],
    'present' => 'Поле :attribute має бути присутнім.',
    'present_if' => 'Поле :attribute має бути присутнім, коли :other дорівнює :value.',
    'present_unless' => 'Поле :attribute має бути присутнім, крім випадку коли :other дорівнює :value.',
    'present_with' => 'Поле :attribute має бути присутнім, коли присутнє :values.',
    'present_with_all' => 'Поле :attribute має бути присутнім, коли присутні всі :values.',
    'prohibited' => 'Поле :attribute заборонене.',
    'prohibited_if' => 'Поле :attribute заборонене, коли :other дорівнює :value.',
    'prohibited_if_accepted' => 'Поле :attribute заборонене, коли :other прийняте.',
    'prohibited_if_declined' => 'Поле :attribute заборонене, коли :other відхилене.',
    'prohibited_unless' => 'Поле :attribute заборонене, крім випадку коли :other входить до :values.',
    'prohibits' => 'Поле :attribute забороняє присутність :other.',
    'regex' => 'Формат поля :attribute є недійсним.',
    'required' => 'Поле :attribute є обовʼязковим.',
    'required_array_keys' => 'Поле :attribute має містити записи для: :values.',
    'required_if' => 'Поле :attribute є обовʼязковим, коли :other дорівнює :value.',
    'required_if_accepted' => 'Поле :attribute є обовʼязковим, коли :other прийняте.',
    'required_if_declined' => 'Поле :attribute є обовʼязковим, коли :other відхилене.',
    'required_unless' => 'Поле :attribute є обовʼязковим, крім випадку коли :other входить до :values.',
    'required_with' => 'Поле :attribute є обовʼязковим, коли присутнє :values.',
    'required_with_all' => 'Поле :attribute є обовʼязковим, коли присутні всі :values.',
    'required_without' => 'Поле :attribute є обовʼязковим, коли відсутнє :values.',
    'required_without_all' => 'Поле :attribute є обовʼязковим, коли відсутні всі :values.',
    'same' => 'Поля :attribute та :other мають збігатися.',
    'size' => [
        'array' => 'Поле :attribute має містити :size елементів.',
        'file' => 'Розмір файлу в полі :attribute має бути :size кілобайт.',
        'numeric' => 'Значення поля :attribute має дорівнювати :size.',
        'string' => 'Довжина рядка в полі :attribute має дорівнювати :size символів.',
    ],
    'starts_with' => 'Поле :attribute має починатися одним із наступних значень: :values.',
    'string' => 'Поле :attribute має бути рядком.',
    'timezone' => 'Поле :attribute має бути дійсним часовим поясом.',
    'unique' => 'Таке значення :attribute вже існує.',
    'uploaded' => 'Не вдалося завантажити :attribute.',
    'uppercase' => 'Поле :attribute має бути у верхньому регістрі.',
    'url' => 'Поле :attribute має бути дійсним URL.',
    'ulid' => 'Поле :attribute має бути дійсним ULID.',
    'uuid' => 'Поле :attribute має бути дійсним UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
