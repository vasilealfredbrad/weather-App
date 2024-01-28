<template>
    <div class="text-white mb-8">
        <div class="places-input text-gray-800">
            <input type="text" class="w-full">
        </div>
        <div class="weather-container font-sans w-128 max-w-lg overflow-hidden shadow-lg mt-4 bg-gray-900 rounded-lg">
            <div class="current-weather flex items-center justify-between px-6 py-8">
                <div class="flex items-center">
                    <div>
                        <div class="text-6xl font-semibold">
                            {{ this.currentTemperature.actual }}
                        </div>
                        <div>Feels like {{ this.currentTemperature.feels }} °C</div>
                    </div>
                    <div class="mx-5">
                        <div class="font-semibold"> {{ this.currentTemperature.summary }}</div>
                        <div> {{ this.locatios.name }}</div>
                    </div>
                </div>
                <canvas ref="iconCurrent" id="iconCurrent" width="96" height="96"></canvas>
                <div>

                </div>
            </div> <!-- END CURRENT WEATHER-->

            <div class="future-weather">
                <div class="future-weather text-sm bg-gray-800 px-6 py-8">
                    <div
                        v-for="(day,index) in daily"
                        :key="day.time"
                        :class="{'mt-8' : index > 5}"
                        class="flex items-center pt-8">
                        <div class="w-1/6 text-lg text-gray-200">
                            {{ day.dt }}
                        </div>
                        <div class="w-4/6 px-4 flex items-center">
                            <canvas ref="iconCurrent" id="iconCurrent" width="96" height="96"></canvas>
                            <div class="ml-3"> {{ day.weather_description }}</div>
                        </div>
                        <div class="w-1/6 text-right">
                            <div> {{ day.temp }} °C</div>
                            <div>{{ day.feels_like }}°C</div>
                        </div>
                    </div>
                </div>
            </div> <!-- END FUTURE WEATHER -->

        </div> <!--    END WEATHER CONTAINER-->
    </div>
</template>

<script>
function findSkyconFunction(keyword) {
    const skyconMappings = {
        "clear": "clear-day",
        "sun": "clear-day",
        "day": "clear-day",
        "night": "clear-night",
        "partly": "partly-cloudy-day",
        "clouds": "cloudy",
        "rain": "rain",
        "sleet": "sleet",
        "snow": "snow",
        "windy": "wind",
        "fog": "fog",
    };

    const lowercaseKeyword = keyword.toLowerCase();

    // look for the key
    for (const key in skyconMappings) {
        if (lowercaseKeyword.includes(key)) {
            return skyconMappings[key];
        }
    }
    return null;
}


export default {
    mounted() {
        this.fetchData()
    },
    data() {
        return {
            currentTemperature: {
                actual: '',
                feels: '',
                max: '',
                min: '',
                summary: '',
                icon: '',
            },
            daily: [],
            locatios: {
                name: 'Tecuci',
                lat: 45.8491,
                lng: 27.4241
            }
        }
    },
    methods: {
        fetchData() {
            const skycons = new Skycons({"color": "white"});

            try {
                fetch(`/api/weather?lat=${this.locatios.lat}&lng=${this.locatios.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log(data)
                        this.currentTemperature.actual = Math.round(data.list[0].temp)
                        this.currentTemperature.feels = Math.round(data.list[0].feels_like)
                        this.currentTemperature.max = Math.round(data.list[0].temp_max)
                        this.currentTemperature.min = Math.round(data.list[0].temp_min)
                        this.currentTemperature.summary = data.list[0].weather_main + ", " + data.list[0].weather[0].description
                        this.currentTemperature.icon = data.list[0].weather[0].icon
                        this.locatios.name = data.city.name
                        this.daily = data.list

                        const icon = findSkyconFunction('Clouds')

                        skycons.add("iconCurrent", icon);
                        skycons.play();
                    })
            } catch (error) {
                console.error("An error occurred:", error.message);
            }
        }
    }
}
</script>
