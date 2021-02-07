
function addNewKey()
{
    let url = api_url + "/api/configuration/create";
    url += "?password=" + api_pass; 
    url += "&key=" + document.querySelector("#newKey").value; 
    url += "&value=" + document.querySelector("#newValue").value; 
    fetch(url)
    .then(data => data.json())
    .then((res)=>{
        console.log(res)
        fetchConfig();
    });
}

function deleteKey(key){
    let url = api_url + "/api/configuration/delete";
    url += "?password=" + api_pass; 
    url += "&deletions=" + key; 
    fetch(url)
    .then(data => data.json())
    .then((res)=>{
        console.log(res)
        fetchConfig();
    });
}

function registerKey(e){
    if (e.keyCode != 13) return ;
    let key = e.currentTarget.getAttribute("key");
    let value = e.currentTarget.value;
    console.log(key, value);
    let url = api_url + "/api/configuration/update";
    url += "?password=" + api_pass; 
    url += "&key=" + key; 
    url += "&value=" + value; 
    fetch(url)
    .then(data => data.json())
    .then((res)=>{
        console.log(res)
        fetchConfig();
    });
}

function fetchConfig(){
    let url = api_url + "/api/configuration/read";
    url += "?password=" + api_pass; 
    fetch(url)
    .then(data => data.json())
    .then((cfg)=>{
        globalConfig = cfg;
        cfgSlot.innerHTML = "";
        Object.keys(cfg).forEach(key => {
            let type = "text";
            if (typeof cfg[key] == "string"){
                if (key.indexOf("_pass") != -1) {
                    type = "password"
                } else {
                    type = "text";
                }
            }
            if (typeof cfg[key] == "number") type = "number"
            cfgSlot.innerHTML += `
                <section class="list-element f-row align-center">
                    <span>${key}</span>
                    <section class="f-filler"></section>
                    <input key="${key}" class="inputElement" type="${type}" value="${cfg[key]}">
                    <button class="ml-1 button red icon" onclick="deleteKey('${key}')" >🗑</button>
                </section>
            `;
        })
        document.querySelectorAll(".inputElement").forEach((elem)=>{
            elem.addEventListener("keyup", registerKey )
        })
        console.log(cfg);
    });

}

let cfgSlot = document.querySelector("#cfgSlot");
let editor = document.querySelector("#editor")
let api_pass = document.querySelector("#m_api_password").value;
let api_url = document.querySelector("#m_api_url").value;
let globalConfig = {};
fetchConfig();