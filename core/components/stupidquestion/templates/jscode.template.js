var el = document.getElementsByName("[[+id]]");
for (var i = 0; i < el.length; i++) {
	el[i].value = "[[+othervalue]]";
	el[i].value = "[[+value]]";
	el[i].parentNode.style.display = "none";
}
