function openMenu(e)
{
    e.preventDefault();
    let target = e.currentTarget;
    let id = target.getAttribute("id");
    selectedModel = globalModels[id];
    contextMenu.style.left = e.clientX - 5  +"px";
    contextMenu.style.top = e.clientY - 5  +"px";
    console.log(e.clientX, e.clientY)
    contextMenu.classList.remove("hidden");
}

function closeMenu()
{
    contextMenu.classList.add("hidden");
}

function fetchModel(){
    let url = api_url + "/api/model/create";
    url += "?password=" + api_pass; 
    url += "&table="+ selectedModel.table;
    fetch(url)
    .then(data => data.json())
    .then((res)=>{
        if (res.message == "DONE"){
            alert("Model Fetched")
        } else {
            alert(res.message);
        }
        readModel();
    });
    closeMenu();
}

function deleteModel(){
    let url = api_url + "/api/model/delete";
    url += "?password=" + api_pass; 
    url += "&model="+ selectedModel.model;
    fetch(url)
    .then(data => data.json())
    .then((res)=>{
        if (res.message == "DONE"){
            alert("Model Deleted")
        } else {
            alert(res.message);
        }
        readModel();
    })
    closeMenu();
}

function readModel()
{
    console.log("Fetching the API")
    let url = api_url + "/api/model/read";
    url += "?password=" + api_pass; 
    console.log(url);
    fetch(url)
    .then(data => data.json())
    .then((models)=>{
        globalModels = models;
        modelSection.innerHTML = ""
        models.forEach( (model, k) => {
            modelSection.innerHTML += `
                <section class="f-row align-center list-element clickable model-section neutral" id="${k}">
                    <section class="f-col f-filler">
                        <span>${model.model} (table : '${model.table}')</span>
                        <section class="f-filler"></section>
                        <span class="discrete">${model.path}</span>
                    </section>
                    <section class="f-row">
                        <span>${model.fetched}</span>
                    </section>
                </section>
            `
        });
        console.log(models);
        document.querySelectorAll(".model-section").forEach(elem => {
            elem.addEventListener("click", openMenu);
            elem.addEventListener("contextmenu", openMenu);
        })
    });
}

let editor = document.querySelector("#editor")
let api_pass = document.querySelector("#m_api_password").value;
let api_url = document.querySelector("#m_api_url").value;
let modelSection = document.querySelector("#modelSection");
let contextMenu = document.querySelector("#context-menu");
contextMenu.addEventListener("mouseleave", closeMenu);
let globalModels = [];
let selectedModel = null;
readModel();