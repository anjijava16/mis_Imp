package com.adnan.zohobooks;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URISyntaxException;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;

import com.google.appengine.labs.repackaged.org.json.JSONArray;
import com.google.appengine.labs.repackaged.org.json.JSONException;
import com.google.appengine.labs.repackaged.org.json.JSONObject;

public class Controller {
	public Controller(){
		itemsObject = new ArrayList<Item>();
		Item item = new Item();
		item.setItemID("158550000000041084");
		item.setName("AZUS");
		item.setRate(10);
		
		itemsObject.add(item);
		
		item = new Item();
		item.setItemID("158550000000041116");
		item.setName("Toshiba");
		item.setRate(20);
		itemsObject.add(item);
		
	}
	private ArrayList<Item> itemsObject = null;

	

	public String createInvoice(String[] quatitys, String[] products) {
		String resultJSON = null;
		String authtoken = "7176f959137817131b4d522d84e08112";
        String organization_id = "57761035";
        String JSONStr = URLEncoder.encode(getSelectedItemJSON(quatitys, products));
        
        final String AUTHTOKEN_PARAM = "authtoken";
        final String ORGNAIZATION_ID_PARAM = "organization_id";
        final String JSONSTRING = "JSONString";
        final String BASE_URL = "https://books.zoho.com/api/v3/invoices?";
		String urlStr ;
		urlStr = BASE_URL + AUTHTOKEN_PARAM + "=" + authtoken + "&"
				+ ORGNAIZATION_ID_PARAM + "=" + organization_id + "&"
				+ JSONSTRING + "=" + JSONStr;
				
		resultJSON = connect(urlStr, "POST");
		
		System.out.println("resultJSON:\n" + resultJSON);
		return resultJSON;

	}
	
	 private String getSelectedItemJSON(String[] quatitys, String[] products) {
         JSONObject invoice = new JSONObject();
         JSONArray items = new JSONArray();

         try {
             invoice.put("customer_id", "158550000000041156");
             int length = quatitys.length;
             for(int i = 0 ; i < length;++i){
//                 View rowItem = listView.getChildAt(i);
//                 double quantity = ((NumberPicker) rowItem.findViewById(R.id.quantity)).getValue();
//                 String itemID = ((Item)((Spinner) rowItem.findViewById(R.id.spinner)).getSelectedItem()).getItemID();
                 
                 double quantity = Double.valueOf(quatitys[i]);
                 String itemID = products[i];
                 JSONObject item = new JSONObject();
                 item.put("item_id",itemID);
                 item.put("quantity",quantity);
                 items.put(item);
             }

             invoice.put("line_items",items);
         } catch (JSONException e) {
             e.printStackTrace();
         }
         return invoice.toString();
     }


	public void loadItems() {
		
		String resultJSON = null;
		String authtoken = "7176f959137817131b4d522d84e08112";
        String organization_id = "57761035";
        
        final String AUTHTOKEN_PARAM = "authtoken";
        final String ORGNAIZATION_ID_PARAM = "organization_id";
        final String BASE_URL = "https://books.zoho.com/api/v3/items?";
		String urlStr ;
		urlStr = BASE_URL + AUTHTOKEN_PARAM + "=" + authtoken + "&"
				+ ORGNAIZATION_ID_PARAM + "=" + organization_id;
				
		resultJSON = connect(urlStr, "GET");
		
		System.out.println("resultJSON:\n" + resultJSON);
		
		setItemsObject(getItemsFromJson(resultJSON));
		
	}

	private ArrayList<Item> getItemsFromJson(String resultJsonStr) {

		// These are the names of the JSON objects that need to be extracted.
		final String ITEMS = "items";
		final String ITEM_ID = "item_id";
		final String ITEM_NAME = "item_name";
		final String RATE = "rate";

		JSONObject itemsJason;
		ArrayList<Item> itemsObject = null;
		try {
			itemsJason = new JSONObject(resultJsonStr);

			JSONArray itemArray = itemsJason.getJSONArray(ITEMS);

			itemsObject = new ArrayList<Item>();
			for (int i = 0; i < itemArray.length(); ++i) {
				JSONObject itemJason = itemArray.getJSONObject(i);
				Item itemObject = new Item(itemJason.getString(ITEM_ID),
						itemJason.getDouble(RATE), itemJason.getString(
								ITEM_NAME).trim());
				itemsObject.add(itemObject);
			}
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return itemsObject;
	}
	
	String connect(String urlStr, String methodType){
		String resultJSON = null;
		HttpURLConnection httpcon;
		// Connect
		try {
			URL url = new URL(urlStr);
			System.out.println("url: \n" + url.toString());
			httpcon = (HttpURLConnection) url.openConnection();
			httpcon.setRequestMethod(methodType);
			httpcon.setConnectTimeout(60000); // 60 Seconds
			httpcon.setReadTimeout(60000); // 60 Seconds
			httpcon.connect();
			BufferedReader br = new BufferedReader(new InputStreamReader(
					httpcon.getInputStream()));

			String line = null;
			StringBuilder sb = new StringBuilder();

			while ((line = br.readLine()) != null) {
				sb.append(line);
			}

			br.close();
			resultJSON = sb.toString();
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
		return resultJSON;
	}

	public ArrayList<Item> getItemsObject() {
		return itemsObject;
	}

	public void setItemsObject(ArrayList<Item> itemsObject) {
		this.itemsObject = itemsObject;
	}
}
