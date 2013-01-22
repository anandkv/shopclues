import java.io.*;
import java.io.File;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.Reader;
import java.util.StringTokenizer;
import java.util.*;
/*
 * To change this template, choose Tools|Templates
 * and open the template in the editor.
 */

/**
 *
 * @author garstech00025
 */
public class ffPostTracker {
    
public static void main (String args[]) {
   // final File folder = new File("/Entertainment/Torrents/www.watchkart.com/titan/");
     try {
     BufferedReader br = new BufferedReader(new FileReader(new File("inputFirstFlight.csv")));
     String line = "";

    ArrayList<String[]> cardList = new ArrayList<String[]>(); // Use an arraylist because we might not know how many cards we need to parse.
	System.out.println("Order No.,Tracking No,Carrier Name,Booked From,Booked Date,Delivered At,Deliver Date,Status Now");
    while((line = br.readLine()) != null) { // Read a single line from the file until there are no more lines to read
        StringTokenizer st = new StringTokenizer(line, ","); // "|" is the delimiter of our input file.
        String[] card = new String[8]; // Each card has 8 fields, so we need room for the 8 tokens.
		int i = 0;
            while(st.hasMoreTokens()) { // For each token in the line that we've read:
            String value = st.nextToken(); // Read the token
            card[i] = value; // Place the token into the ith "column"
       	i++;
        }
        cardList.add(card); // Add the card's info to the list of cards.
    }

    for(int i = 0; i < cardList.size(); i++) {
    //cardList.get(i)[2];
	    System.out.print(cardList.get(i)[0]+","+cardList.get(i)[1]+","+cardList.get(i)[2]+",");
       Process p = Runtime.getRuntime().exec("php firstFlight.php "+ cardList.get(i)[1]);
	   BufferedReader is = new BufferedReader(new InputStreamReader(p.getInputStream()));

	    while ((line = is.readLine()) != null)
			System.out.println(line);
    		System.out.flush();
	        	p.waitFor();  // wait for process to complete
    	}
    }
     catch (Exception ex) {
            Logger.getLogger(FileReaderFromSystem.class.getName()).log(Level.SEVERE, null, ex);
        }
   }



}
