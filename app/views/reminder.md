# Monkey Views

Views are classical php files that you can call with the
`Renderer` component

As PHP is already a templating tool, quite simple but it 
still do the trick for simple uses, you can have something 
like this

Monkey's philosophy is to stay quite simple, BUT, you can
still add a renderer component with composer if you wish so

<ul>
    <?php for ($i=0; $i<5; $i++) { ?>
        <li>Some Example : <?= $i ?></li>
    <?php } ?>
</ul>

&lt;ul&gt;
    &lt;?php for ($i=0; $i&lt;5; $i++) { ?&gt;
        &lt;li&gt;Some Example : &lt;?= $i ?&gt;&lt;/li&gt;
    &lt;?php } ?&gt;
&lt;/ul&gt;