
function openEditor() { document.querySelector("#editor").classList.remove("hidden"); }
function closeEditor() { document.querySelector("#editor").classList.add("hidden"); }

function saveRoute(id)
{
    let route = getEditorRoute();
    let url = api_url + "/api/route/update";
    url += "?route=" + JSON.stringify(route); 
    url += "&index=" + id;
    url += "&password=" + api_pass; 
    fetch(url)
    .then(data => data.json())
    .then((data)=>{
        if (data.message === "DONE"){
            positive("Route Updated !");
        } else {
            negative(data.message)
        }
        fetchRoutes()
    });
    closeEditor()
    info("Loading...")
}

function deleteRoute(id)
{
    console.log("delete")
    let url = api_url + "/api/route/delete";
    url += "?index=" + id; 
    url += "&password=" + api_pass; 
    fetch(url)
    .then(data => data.json())
    .then((data)=>{
        if (data.message === "DONE"){
            positive("Route Deleted !");
        } else {
            negative(data.message)
        }
        fetchRoutes()
    });
    closeEditor()
    info("Loading...")
}

function fillEditor(id) 
{
    openEditor();
    let route = globalRoutes[id];
    console.log(route);

    document.querySelector("#editor_path").value = route.path;
    document.querySelector("#editor_callback").value = route.callback;
    document.querySelector("#editor_name").value = route.name;
    document.querySelector("#editor_middlewares").value = route.middlewares;
    document.querySelector("#editor_methods").value = route.methods;
    document.querySelector("#editor_cancelButton").addEventListener("click", closeEditor);
    document.querySelector("#editor_addButton").addEventListener("click", (e)=>{
        e.preventDefault();
        saveRoute(id)
    });
    document.querySelector("#editor_deletebutton").addEventListener("click", (e)=>{
        e.preventDefault();
        deleteRoute(id)
    })
}

function getEditorRoute() 
{
    let route = {
        path : document.querySelector("#editor_path").value,
        callback : document.querySelector("#editor_callback").value,
        name : document.querySelector("#editor_name").value,
        middlewares : document.querySelector("#editor_middlewares").value.replace(/ /g, "").split(","),
        methods: document.querySelector("#editor_methods").value.replace(/ /g, "").split(",")
    }
    return route;
}


function createRoute()
{
    console.log("add!");
    let route = {
        path : document.querySelector("#path").value,
        callback : document.querySelector("#callback").value,
        name : document.querySelector("#name").value,
        middlewares : document.querySelector("#middlewares").value.replace(/ /g, "").split(","),
        methods: document.querySelector("#methods").value.replace(/ /g, "").split(",")
    }
    let url = api_url + "/api/route/create";
    url += "?route=" + JSON.stringify(route); 
    url += "&password=" + api_pass; 
    console.log(url);
    fetch(url)
    .then(data => data.json())
    .then((data)=>{
        if (data.message === "DONE"){
            positive("Route created !");
        } else {
            negative(data.message)
        }
        fetchRoutes()
    });
    closeEditor()
    info("Loading...")
}

function fetchRoutes()
{
    let url = api_url + "/api/route/read";
    url += "?password=" + api_pass; 
    console.log(url);
    fetch(url)
    .then(data => data.json())
    .then((routes)=>{
        let id = 0;
        document.querySelector("#routesSlot").innerHTML = "";
        console.log(routes);
        routes = routes.map((route)=>{
            route.methods = route.methods.join(", ")
            route.middlewares = route.middlewares.join(", ")
            return route;
        })

        globalRoutes = routes;

        routes.forEach((route)=>{
            let routeHTML = `
                <section class="list-element clickable f-row align-center" onclick="fillEditor(${id})">
                    <span>${route.path}</span>
                    <section class="f-filler"></section>
                    <span>${route.callback}</span>
                </section>
            `
            console.log(route)
            document.querySelector("#routesSlot").innerHTML += routeHTML;
            id++;
        });

        if (routes.length == 0){
            document.querySelector("#routesSlot").innerHTML = "<span>No route defined !</span>";
        }
        
        document.querySelector("#addButton").addEventListener("click", createRoute);
        closeEditor();
    });
}

let editor = document.querySelector("#editor")
let api_pass = document.querySelector("#m_api_password").value;
let api_url = document.querySelector("#m_api_url").value;
let globalRoutes = [];
fetchRoutes();