import java.util.Date;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.io.*;
import java.net.*;
import java.util.Timer;
import java.util.TimerTask;

import javax.servlet.ServletContext;
import javax.servlet.ServletContextAttributeEvent;
import javax.servlet.ServletContextAttributeListener;
import javax.servlet.ServletContextEvent;
import javax.servlet.ServletContextListener;

/**
 * Creates the scheduled tasks
 */
class Scheduler implements ServletContextAttributeListener, ServletContextListener {
	private ServletContext context = null;
	public void contextInitialized(ServletContextEvent event) {
		this.context = event.getServletContext();
		System.out.println("\n+------------------------------+");
		System.out.println("| Reviewedex scheduler started |");
		System.out.println("+------------------------------+\n");
		
		Timer timer = new Timer();
		
		System.out.println("> Running Heartbeat every minute.");
		timer.schedule(new Heartbeat(), 0, 1000 * 60);
		
		System.out.println("> Running MessageHandler every 5 seconds.\n");
		timer.schedule(new MessageHandler(), 0, 1000 * 5);
	}
	public void contextDestroyed(ServletContextEvent event) {
		System.out.println("\n+---------------------------------+");
		System.out.println("| Reviewedex scheduler terminated |");
		System.out.println("+---------------------------------+\n");
    	this.context = null;
    	System.exit(1);
	}
	public void attributeAdded (ServletContextAttributeEvent scab) {
		// Attribute added
	}
	public void attributeRemoved (ServletContextAttributeEvent scab) {
		// Attribute remove
	}
	public void attributeReplaced (ServletContextAttributeEvent scab) {
		// Attribute replaced
	}
}

/**
 * Base scheduler class
 * Whenever it runs, it outputs a timestamp and the command a message explaining what it is doing
 * It then runs the relevant PHP script and outputs the result to the console
 */
abstract class SchedulerTask extends TimerTask {
	String msg;
	String file;
	public String GetDate() {
		return (new SimpleDateFormat("yyyy/MM/dd HH:mm:ss")).format(new Date());
	}
	public String execPHP(String fileName) {
		String line;
		StringBuilder output = new StringBuilder();
		try {
			BufferedReader input = new BufferedReader(new InputStreamReader((Runtime.getRuntime().exec("php "+fileName)).getInputStream()));
			while ((line = input.readLine()) != null) {
				output.append(line);
			}
			input.close();
		} catch (Exception err) {
			System.out.println("Failed to execute PHP script: "+fileName);
		}
		return output.toString();
	}
	public void run() {
		System.out.println(GetDate()+" "+msg);
		System.out.println(execPHP(file));
	}
}

/**
 * Heartbeat - Calls the PHP script to check all other server statuses
 */
class Heartbeat extends SchedulerTask {
	public Heartbeat() {
		msg = "Doing heartbeat";
		file = "../../scheduled_heartbeat.php";
	}
}

/**
 * MessageHandler - Calls the PHP script to handle all messages in the queue
 */
class MessageHandler extends SchedulerTask {
	public MessageHandler() {
		msg = "Handling messages";
		file = "../../scheduled_messageHandler.php";
	}
}
