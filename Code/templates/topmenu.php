<?php
// Bilingual navigation menu. In the following, note that the "absolute"
// URLs will be rebased by Fantastic Windmill into URLs relative to the
// web site's root folder (whatever that folder is)
if ($page->data["lang"] === "en")
{
?>
<div id="navigation">
<h2>Navigation</h2>
<ul>
<li><a href="/home.html">Home</li>
<li><a href="/en/square.html">Square</li>
<li><a href="/en/circle.html">Circle</li>
<li><a href="/posts/list.html">List of posts</li>
</ul>
</div>
<?php
}
elseif ($page->data["lang"] === "fr")
{
?>
<div id="navigation">
<h2>Navigation</h2>
<ul>
<li><a href="/index.html">Accueil</li>
<li><a href="/fr/carre.html">Carré</li>
<li><a href="/fr/cercle.html">Cercle</li>
<li><a href="/posts/liste.html">Liste des publications</li>
</ul>
</div>
<?php
}
?>
