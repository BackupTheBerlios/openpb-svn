<h3>Configurator</h3>
<p>OPT configurator is a simple script, which allows you to build your own versions of Open Power Template
parser. This gives you lots of benefits: you may remove the features from the source code you are not using
or are unncessary. When you upload your application into the web, you will probably also remove all
the debug options. OPT will work faster then.</p>
<p>You must specify three things: 1/ the directory, where the original OPT code is placed; 2/ the output
directory, where will be placed the "new" OPT; 3/ the features you want to keep. Here are their descriptions:
</p>
{show=directives}
<ul>
{section}
<li><strong>{$directives.title}</strong> - {$directives.description}</li>
{/section}
</ul>
{showelse}
<p>No directives found.</p>
{/show}

<h3>Compiler</h3>
<p>When the "performance" directive is set in the OPT configuration, the library does not check, whether
the templates are modified. This means the programmer must recompile them on his own. This tool is intended
to those ones, who have already run their websites and do not want to change the settings just to make
tiny changes. They can compile the template here and send the precompiled version to the server.</p>
<p>The screen shows two text fields: "root" and "compile" directory, which point to the directories with
the source and compiled templates. Below, there is a list of templates from these directories. You can:</p>
<ol>
 <li>Recompile all the templates at once (for a big number of templates it may take several seconds).</li>
 <li>Recompile the selected templates</li>
 <li>Remove the compiled version</li>
</ol>

<h3>Other issues</h3>
<p>The script remembers the last used settings, so that you do not have to remember, what you set lately.
Remember that PHP must have a write access to the script directory, where the settings are stored.</p>
