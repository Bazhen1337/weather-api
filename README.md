# Документация API Погодного Сервиса

## Базовый URL
`https://ваш-домен/api/`

## Авторизация
API не требует авторизации.

## Endpoints

### 1. Получение погоды по названию города

**URL:** `/weather`

**Метод:** `GET`

**Параметры:**

| Параметр | Тип    | Обязательный | Описание                          | Допустимые значения               |
|----------|--------|--------------|-----------------------------------|-----------------------------------|
| city     | string | Да           | Название города                   | Любая строка до 100 символов      |
| unit     | string | Нет          | Единицы измерения температуры     | `celsius` (по умолчанию), `fahrenheit` |
| lang     | string | Нет          | Язык ответа                       | `ru` (по умолчанию), другие языки |

**Пример запроса:**
```
GET /api/weather?city=Москва&unit=celsius&lang=ru
```

**Успешный ответ (200 OK):**
```json
{
    "success": true,
    "city": "Москва",
    "weather": {
        "temperature": 15.5,
        "unit": "celsius",
        "description": "ясно",
        "icon": "https://openweathermap.org/img/wn/01d@4x.png"
    },
    "wind": {
        "speed": 3.2,
        "direction": "северо-западный"
    },
    "atmosphere": {
        "pressure": 1012,
        "humidity": 65
    },
    "precipitation": {
        "probability": 10
    },
    "timestamp": "2023-05-15 14:30:45"
}
```

**Возможные ошибки:**

- `400 Bad Request` - Неверные параметры запроса
- `404 Not Found` - Город не найден
- `422 Unprocessable Entity` - Ошибка валидации
- `500 Internal Server Error` - Ошибка сервера

### 2. Получение погоды по координатам

**URL:** `/location`

**Метод:** `GET`

**Параметры:**

| Параметр | Тип    | Обязательный | Описание                          |
|----------|--------|--------------|-----------------------------------|
| lat      | float  | Да           | Широта                            |
| lon      | float  | Да           | Долгота                           |
| unit     | string | Нет          | Единицы измерения температуры     |
| lang     | string | Нет          | Язык ответа                       |

**Пример запроса:**
```
GET /api/location?lat=55.7558&lon=37.6176&unit=fahrenheit
```

**Успешный ответ (200 OK):**
Аналогичен ответу от `/weather`, но город определяется автоматически.

**Возможные ошибки:**

- `422 Unprocessable Entity` - Ошибка валидации
- `500 Internal Server Error` - Ошибка сервера

## Объекты ответа

### Погодные данные
```json
{
    "temperature": 15.5,
    "unit": "celsius",
    "description": "ясно",
    "icon": "URL иконки"
}
```

### Ветер
```json
{
    "speed": 3.2,
    "direction": "северо-западный"
}
```

### Атмосфера
```json
{
    "pressure": 1012,
    "humidity": 65
}
```

### Осадки
```json
{
    "probability": 10
}
```

## Примеры ошибок

**Ошибка валидации (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "city": ["Поле city обязательно для заполнения."]
    }
}
```

**Ошибка сервера (500):**
```json
{
    "success": false,
    "message": "Server error",
    "error": "Error while requesting weather API"
}
```

## Лимиты и ограничения
- Максимальная длина названия города: 100 символов
- Поддерживаемые языки: ru, en и другие (зависит от API OpenWeatherMap)

## Версия API
Текущая версия: 1.0
