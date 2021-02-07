
function listArray(array){
    let res = `<ul>`
    array.forEach(elem => {
        res += `<li>${elem}</li>`
    })
    if (array.length === 0) res += "Empty array"
    res += "</ul>";
    return res;
}

function fetchSummary(){
    let url = api_url + "/api/index/read";
    url += "?password=" + api_pass; 
    fetch(url)
    .then(data => data.json())
    .then((summary)=>{
        document.querySelector("#summarySlot").innerHTML = "";
        Object.keys(summary).forEach(key => {
            if (Array.isArray(summary[key])) summary[key] = listArray(summary[key]);
            document.querySelector("#summarySlot").innerHTML += `
                <section class="list-element f-row align-center">
                    <span>${key}</span>
                    <section class="f-filler"></section>
                    <span>${summary[key]}</span>
                </section>
            `
        })
    });
}

let api_pass = document.querySelector("#m_api_password").value;
let api_url = document.querySelector("#m_api_url").value;
let globalConfig = {};
fetchSummary();