
function getSections()
{
    let sections = document.querySelectorAll(".content-section");
    sections = Array.from(sections);
    sections = sections.filter((elem)=>{
        return elem.id != "";
    })
    return sections;
}

/* ------------------------ SUMMARY PART ------------------------ */

// Thanks AlexZ & 7ochem !
function isVisible(el) {
    return (el.offsetParent !== null)
}

function getTitle(element){
    return document.querySelector(`#${element.id} h1`).innerText;
}

function getLink(element){
    return `<li><a href='#${element.id}'>${getTitle(element)}</a></li>`
}

function buildSummary()
{
    let sections = getSections();
    sections = sections.filter(isVisible);


    document.querySelector("#asideMenu").innerHTML = "";
    let links = sections.map(getLink);
    links.forEach(elem => {
        document.querySelector("#asideMenu").innerHTML += elem;
    })
}

/* ------------------------ SEARCH PART ------------------------ */

function search()
{
    let toSearch = document.querySelector("#searchInput").value.toLowerCase();
    let sections = getSections();
    let titles = sections.map(elem => [elem, getTitle(elem).toLowerCase()]);
    titles = titles.filter( elem => {
        return elem[1].indexOf(toSearch) != -1
    });
    titles = titles.map(elem => elem[0]);
    sections.forEach( section => {
        if (titles.includes(section)){
            section.classList.remove("hidden");
        } else {
            section.classList.add("hidden");
        }
    });
    buildSummary();
}

buildSummary();
document.querySelector("#searchInput").addEventListener("keyup", search);