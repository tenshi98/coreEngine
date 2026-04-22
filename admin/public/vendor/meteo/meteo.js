class WeatherWidget{

    constructor(container, Base, Type, latitude, longitude){
        this.container = container;
        this.Base      = Base;
        this.Type      = Type;
        this.loadWeather(latitude,longitude);
    }

    async loadWeather(lat,lon){
        let url  = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weather_code&timezone=auto`;

        let res  = await fetch(url);
        let data = await res.json();

        switch (this.Type) {
            case 1:
                this.getCity(lat,lon);
                this.renderCurrent_v1(data.current,data.daily);
                this.renderWeek_v1(data.daily);
                break;
            case 2:
                this.getCity(lat,lon);
                this.renderCurrent_v1(data.current,data.daily);
                break;
            case 3:
                this.getCity(lat,lon);
                this.renderWeek_v1(data.daily);
                break;
            case 4:
                this.getCity(lat,lon);
                this.renderCurrent_v1(data.current,data.daily);
                break;
            case 5:
                this.getCity(lat,lon);
                this.renderCurrent_v2(data.current,data.daily);
                break;
            case 6:
                this.getCity(lat,lon);
                this.renderCurrent_v3(data.current,data.daily);
                this.renderWeek_v2(data.daily);
                break;
            case 7:
                this.getCity(lat,lon);
                this.renderCurrent_v3(data.current,data.daily);
                break;
            case 8:
                this.getCity(lat,lon);
                this.renderWeek_v2(data.daily);
                break;
            case 9:
                this.getCity(lat,lon);
                this.renderCurrent_v1(data.current,data.daily);
                this.renderWeek_v3(data.daily);
                break;
            case 10:
                this.getCity(lat,lon);
                this.renderCurrent_v1(data.current,data.daily);
                break;
            case 11:
                this.getCity(lat,lon);
                this.renderWeek_v3(data.daily);
                break;

        }
        this.showContent();
    }

    qs(selector){
        return this.container.querySelector(selector);
    }

    async getCity(lat,lon){

        let url  = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

        let res  = await fetch(url,{
            headers:{
                "Accept":"application/json"
            }
        });

        let data = await res.json();

        if(data.address){
            let city = data.address.suburb ? data.address.suburb : data.address.county;
            this.qs(".weatherTitle").innerText = city;
        }
    }

    showContent(){
        this.qs(".weatherLoader").style.display="none";

        let content = this.qs(".weatherContent");

        setTimeout(()=>{
            content.classList.add("show");
        },50);
    }

    iconMap(code){

        const map={
            0:"clear-day",
            1:"partly-cloudy-day",
            2:"partly-cloudy-day",
            3:"cloudy",
            45:"fog",
            48:"fog",
            51:"rain",
            61:"rain",
            63:"rain",
            71:"snow",
            80:"rain",
            95:"rain"
        };

        return map[code] || "cloudy";
    }

    renderCurrent_v1(current,daily){
        this.qs(".weatherTemp").innerHTML     = current.temperature_2m+"°C";
        this.qs(".weatherWind").innerText     = current.wind_speed_10m;
        this.qs(".weatherHumidity").innerText = current.relative_humidity_2m;
        this.qs(".weatherRain").innerText     = daily.precipitation_probability_max[0];
        this.qs(".weatherMin").innerText      = daily.temperature_2m_min[0]+"°";
        this.qs(".weatherMax").innerText      = daily.temperature_2m_max[0]+"°";
        let icon = this.iconMap(current.weather_code);
        this.qs(".weatherIcon").src = this.Base+"/img/meteo/icons/"+icon+".svg";
    }

    renderCurrent_v2(current,daily){
        this.qs(".weatherTemp").innerHTML     = current.temperature_2m+"°C";
        this.qs(".weatherWind").innerText     = current.wind_speed_10m;
        this.qs(".weatherHumidity").innerText = current.relative_humidity_2m;
        this.qs(".weatherRain").innerText     = daily.precipitation_probability_max[0];
        this.qs(".weatherMin").innerText      = daily.temperature_2m_min[0]+"°";
        this.qs(".weatherMax").innerText      = daily.temperature_2m_max[0]+"°";
    }

    renderCurrent_v3(current,daily){
        this.qs(".weatherTemp").innerHTML     = current.temperature_2m+"°C";
        this.qs(".weatherMin").innerText      = daily.temperature_2m_min[0]+"°";
        this.qs(".weatherMax").innerText      = daily.temperature_2m_max[0]+"°";
    }

    renderWeek_v1(daily){
        let container = this.qs(".weatherWeek");
        for(let i=1;i<daily.time.length;i++){
            let date      = new Date(daily.time[i]);
            let icon      = this.iconMap(daily.weather_code[i]);
            let col       = document.createElement("div");
            col.className = "col weather-day";
            col.innerHTML=`
                <div>${date.toLocaleDateString("es",{weekday:"short"})}</div>
                <img src="${this.Base}/img/meteo/icons/${icon}.svg">
                <div>${daily.temperature_2m_max[i]}°</div>
                <div style="font-size:12px;color:#777">
                🌧 ${daily.precipitation_probability_max[i]}%
                </div>
            `;
            container.appendChild(col);
        }
    }

    renderWeek_v2(daily){
        let container = this.qs(".weatherWeek");
        for(let i=1;i<daily.time.length;i++){
            let date      = new Date(daily.time[i]);
            let icon      = this.iconMap(daily.weather_code[i]);
            let col       = document.createElement("div");
            col.className = "col weather-day";
            col.innerHTML=`
                <li>
                    <span class="date">${date.toLocaleDateString("es",{weekday:"short"})}</span>
                    <span class="lnr lnr-sun condition">
                        <img style="width: 32px;" src="${this.Base}/img/meteo/icons/${icon}.svg">
                        ${daily.temperature_2m_max[i]}°
                        🌧 ${daily.precipitation_probability_max[i]}%
                    </span>
                </li>
            `;
            container.appendChild(col);
        }
    }

    renderWeek_v3(daily){
        let container = this.qs(".weatherWeek");
        for(let i=1;i<4;i++){
            let date      = new Date(daily.time[i]);
            let icon      = this.iconMap(daily.weather_code[i]);
            let col       = document.createElement("div");
            col.className = "col-md-4";
            col.innerHTML=`
                <div class="day">
                    <div>${date.toLocaleDateString("es",{weekday:"short"})}</div>
                    <img src="${this.Base}/img/meteo/icons/${icon}.svg">
                    <div>${daily.temperature_2m_max[i]}°</div>
                    <div style="font-size:12px;color:#777">
                    🌧 ${daily.precipitation_probability_max[i]}%
                    </div>
                </div>
            `;
            container.appendChild(col);
        }
    }

}
