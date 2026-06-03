<?php
$options = get_option('dci_options');

$city = $options['meteo_city'] ?? '';
$apiKey = $options['meteo_api'] ?? '';

// ❌ se city vuota → non mostra nulla
if (empty($city)) {
    return;
}
?>

<section class="weather-section">
  <div class="container">

    <h1 class="weather-title">Meteo <?php echo esc_html($city); ?></h1>

    <div class="weather-grid"></div>

  </div>
</section>




<style>

/* ===== METEO ===== */
.weather-section {
  padding: 60px 0;
}

.weather-title {
  font-size: 32px;
  margin-bottom: 30px;
}

.weather-grid {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

/* CARD */
.weather-card {
  background: #FFFFFF;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  flex: 1;
  min-width: 180px;
  box-shadow: 
  0 4px 10px rgba(0,0,0,0.05),
  0 15px 40px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}

/* OGGI PIÙ GRANDE */
.weather-card.big {
  flex: 2;
  min-width: 300px;
}

/* TITOLI */
.weather-day {
  font-weight: bold;
  margin-bottom: 10px;
}

/* CONTENUTO */
.weather-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.weather-info {
  text-align: left;
  font-size: 14px;
  line-height: 1.6;
}

.weather-icon img {
  width: 70px;
}

/* DESCRIZIONE */
.weather-desc {
  margin-top: 10px;
  font-size: 14px;
  text-transform: capitalize;
}

/* MOBILE */
@media (max-width: 768px) {
  .weather-grid {
    flex-direction: column;
  }
}

</style>

<script>
const weatherCity = "<?php echo esc_js($city); ?>";
const weatherApiKey = "<?php echo esc_js($apiKey); ?>";
</script>

<script>
(function() {
  function initWeather() {
    const section = document.querySelector(".weather-section");
    const container = section ? section.querySelector(".weather-grid") : null;

    if (!section || !container || section.dataset.weatherLoaded === "true") {
      return;
    }

    const city = weatherCity;
    const apiKey = weatherApiKey;

    if (!city || !apiKey) {
      container.innerHTML = "<p>Meteo non configurato.</p>";
      return;
    }

    const apiUrl = `https://api.openweathermap.org/data/2.5/forecast?q=${encodeURIComponent(city)}&appid=${encodeURIComponent(apiKey)}&units=metric&lang=it`;
    const cacheKey = `dci_weather_${city}_${apiKey}`;
    const cacheTtl = 30 * 60 * 1000;

    function getCachedWeather() {
      try {
        const cached = JSON.parse(localStorage.getItem(cacheKey) || "null");

        if (cached && cached.timestamp && (Date.now() - cached.timestamp) < cacheTtl && cached.data) {
          return cached.data;
        }
      } catch (error) {
        localStorage.removeItem(cacheKey);
      }

      return null;
    }

    function setCachedWeather(data) {
      try {
        localStorage.setItem(cacheKey, JSON.stringify({
          timestamp: Date.now(),
          data: data
        }));
      } catch (error) {
        // Cache browser non disponibile o piena: ignoro senza bloccare il meteo.
      }
    }

    async function getWeatherData() {
      const cached = getCachedWeather();

      if (cached) {
        return cached;
      }

      const response = await fetch(apiUrl);
      const data = await response.json();

      if (!data || !Array.isArray(data.list)) {
        throw new Error("Risposta meteo non valida");
      }

      setCachedWeather(data);
      return data;
    }

    async function updateWeather() {
      section.dataset.weatherLoaded = "true";
      container.innerHTML = "<p>Caricamento meteo...</p>";

      try {
        const data = await getWeatherData();
        const daily = {};

        data.list.forEach(item => {
          const date = item.dt_txt.split(" ")[0];

          if (!daily[date]) {
            daily[date] = {
              temps: [],
              humidity: [],
              icons: [],
              desc: item.weather[0].description
            };
          }

          daily[date].temps.push(item.main.temp);
          daily[date].humidity.push(item.main.humidity);
          daily[date].icons.push(item.weather[0].icon);
        });

        const days = Object.keys(daily).slice(0, 5);

        container.innerHTML = "";

        days.forEach((day, index) => {

          const d = daily[day];

          const avgTemp = Math.round(d.temps.reduce((a,b)=>a+b)/d.temps.length);
          const minTemp = Math.round(Math.min(...d.temps));
          const maxTemp = Math.round(Math.max(...d.temps));
          const avgHumidity = Math.round(d.humidity.reduce((a,b)=>a+b)/d.humidity.length);

          const icon = d.icons[Math.floor(d.icons.length/2)];

          const dateObj = new Date(day);
          const dayName = index === 0
            ? "OGGI"
            : dateObj.toLocaleDateString("it-IT", { weekday: 'long' }).toUpperCase();

          container.innerHTML += `
            <div class="weather-card ${index === 0 ? 'big' : ''}">
              <div class="weather-day">${dayName}</div>

              <div class="weather-content">
                <div class="weather-info">
                  <div>Temp. media ${avgTemp}°C</div>
                  <div>Max ${maxTemp}°C</div>
                  <div>Min ${minTemp}°C</div>
                  <div>Umidità ${avgHumidity}%</div>
                </div>

                <div class="weather-icon">
                  <img src="https://openweathermap.org/img/wn/${icon}@2x.png" alt="" loading="lazy">
                </div>
              </div>

              <div class="weather-desc">${d.desc}</div>
            </div>
          `;
        });

      } catch (error) {
        console.error("Errore meteo:", error);
        container.innerHTML = "<p>Errore nel caricamento del meteo.</p>";
      }
    }

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(function(entries) {
        if (entries.some(entry => entry.isIntersecting)) {
          observer.disconnect();
          updateWeather();
        }
      }, { rootMargin: '200px 0px' });

      observer.observe(section);
      return;
    }

    updateWeather();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initWeather);
  } else {
    initWeather();
  }
}());
</script>
