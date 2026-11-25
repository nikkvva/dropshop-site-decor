// Подсказки Новой Почты на оформлении заказа (наш сервер: /np/cities, /np/points).
// Отделения города приходят списком целиком, почтоматы — поиском по мере ввода (их тысячи).
// Если API не отвечает, подсказок просто нет — поля работают как обычный текст.
(function () {
   const form = document.getElementById('checkout-form');
   if (!form) return;

   const city = form.querySelector('[name=city]');
   const cityRef = form.querySelector('[name=city_ref]');
   const point = form.querySelector('[name=delivery_point]');
   const cityList = document.getElementById('np-cities');
   const pointList = document.getElementById('np-points');
   const placeholders = { branch: 'Номер або адреса відділення', postomat: 'Почніть вводити номер або вулицю поштомату' };
   let refs = {}; // название из подсказки → ref города
   let cityTimer, pointTimer;
   let loadId = 0; // пришёл ответ на устаревший запрос — не показываем

   function fill(list, values) {
      list.replaceChildren(...values.map((value) => {
         const option = document.createElement('option');
         option.value = value;
         return option;
      }));
   }

   async function getJson(url) {
      try {
         const response = await fetch(url, { headers: { Accept: 'application/json' } });
         return response.ok ? await response.json() : [];
      } catch (e) {
         return [];
      }
   }

   function type() {
      return form.querySelector('[name=delivery_type]:checked').value;
   }

   async function loadPoints(query = '') {
      const id = ++loadId;
      point.placeholder = placeholders[type()];
      if (!cityRef.value) {
         fill(pointList, []);
         return;
      }
      if (type() === 'branch') point.placeholder = 'Завантажуємо список відділень…';
      const points = await getJson(form.dataset.pointsUrl + '?city_ref=' + cityRef.value + '&type=' + type() + '&q=' + encodeURIComponent(query));
      if (id !== loadId) return;
      fill(pointList, points);
      point.placeholder = placeholders[type()];
   }

   city.addEventListener('input', () => {
      // выбрали город из подсказки
      if (refs[city.value]) {
         if (cityRef.value !== refs[city.value]) {
            cityRef.value = refs[city.value];
            point.value = '';
            loadPoints();
         }
         return;
      }

      // печатают дальше — ищем подсказки с паузой, чтобы не слать запрос на каждую букву
      cityRef.value = '';
      fill(pointList, []);
      clearTimeout(cityTimer);
      const query = city.value.trim();
      if (query.length < 2) return;
      cityTimer = setTimeout(async () => {
         const cities = await getJson(form.dataset.citiesUrl + '?q=' + encodeURIComponent(query));
         refs = {};
         cities.forEach((c) => { refs[c.name] = c.ref; });
         fill(cityList, cities.map((c) => c.name));
      }, 300);
   });

   // почтомат: ищем по введённому тексту (выбор из подсказки — не новый поиск)
   point.addEventListener('input', () => {
      if (type() !== 'postomat' || !cityRef.value) return;
      if ([...pointList.options].some((o) => o.value === point.value)) return;
      clearTimeout(pointTimer);
      const query = point.value.trim();
      if (!query) return;
      pointTimer = setTimeout(() => loadPoints(query), 400);
   });

   form.querySelectorAll('[name=delivery_type]').forEach((radio) => {
      radio.addEventListener('change', () => {
         point.value = '';
         loadPoints();
      });
   });

   // двойной клик по «Підтвердити» не должен создать два заказа
   form.addEventListener('submit', () => {
      form.querySelector('[type=submit]').disabled = true;
   });

   // вернулись на форму после ошибки — город уже выбран
   loadPoints();
})();
