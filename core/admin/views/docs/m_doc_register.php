<section class="content-section main-section" id="m_register">
    <h1>Register</h1>
    <p>
        The monkey register is here to store your data and retrieve it quickly, to summarize it :
        it's an interface with serrialized objects stored in files (.ser), for example : Monkey use 
        it own register to store your application's routes
    </p>
    <p>
        Most of the register functions are similar to the <code>Monkey\Config</code> one
    </p>
<pre>
// Set the foo key to the given array and save a .ser file
Register::set("foo", ["bar"=>"blah"])

// Get the foo bar
Register::get("foo")

// Initialize the component
// Create the store directory if inexistant and load its content
Register::init()

// Write the content of the "foo" key into a ser file 
// Note : this function is automatically called by 'set'
Register::write("foo")

// Load the .ser files into the register 
// Note : this function is automatically called by 'init'
Register::load_files()
</pre>
    <section class="info-section">
    Note : The directory where the .ser files are stored can be edited by changing <code>register_store</code>
    in monkey.ini
    </section>
</section>