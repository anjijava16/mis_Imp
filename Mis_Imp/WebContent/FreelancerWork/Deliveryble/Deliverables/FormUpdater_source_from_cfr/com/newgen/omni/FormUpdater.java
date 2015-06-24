/*
 * Decompiled with CFR 0_101.
 * 
 * Could not load the following classes:
 *  com.itextpdf.text.DocumentException
 *  com.itextpdf.text.pdf.AcroFields
 *  com.itextpdf.text.pdf.PdfReader
 *  com.itextpdf.text.pdf.PdfStamper
 */
package com.newgen.omni;

import com.itextpdf.text.DocumentException;
import com.itextpdf.text.pdf.AcroFields;
import com.itextpdf.text.pdf.PdfReader;
import com.itextpdf.text.pdf.PdfStamper;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.PrintStream;
import java.io.Reader;
import java.util.Enumeration;
import java.util.Properties;

public class FormUpdater {
    public static void main(String[] args) throws IOException, DocumentException, NumberFormatException {
        Properties prop;
        StringBuffer contents;
        prop = null;
        FileInputStream fin = null;
        try {
            prop = new Properties();
            fin = new FileInputStream("forms\\" + args[0] + ".properties");
            prop.load(fin);
        }
        catch (Exception e) {
            e.printStackTrace();
        }
        finally {
            if (fin != null) {
                fin = null;
            }
        }
        String XMLFileName = prop.getProperty("XMLFile");
        File file = new File("forms\\" + XMLFileName);
        contents = new StringBuffer();
        BufferedReader reader = null;
        try {
            reader = new BufferedReader(new FileReader(file));
            String text = null;
            while ((text = reader.readLine()) != null) {
                contents.append(text).append(System.getProperty("line.separator"));
            }
        }
        catch (FileNotFoundException e) {
            e.printStackTrace();
        }
        catch (IOException e) {
            e.printStackTrace();
        }
        finally {
            try {
                if (reader != null) {
                    reader.close();
                }
            }
            catch (IOException e) {
                e.printStackTrace();
            }
        }
        PdfReader pdfReader = new PdfReader("forms\\" + args[0] + ".pdf");
        PdfStamper filledOutForm = new PdfStamper(pdfReader, (OutputStream)new FileOutputStream("forms\\" + args[1] + ".pdf"));
        AcroFields form = filledOutForm.getAcroFields();
        Enumeration e = prop.propertyNames();
        while (e.hasMoreElements()) {
            String key = (String)e.nextElement();
            if (key.equalsIgnoreCase("XMLFile")) continue;
            System.out.println("Key: " + key);
            String XMLField = prop.getProperty(key);
            System.out.println("XML Field: " + XMLField);
            if (XMLField.indexOf("&") != -1) {
                System.out.println("Concatenating");
                String finalValue = "";
                String[] fields = XMLField.split("&");
                for (int x = 0; x < fields.length; ++x) {
                    System.out.println("Field " + String.valueOf(x) + ": " + fields[x]);
                    finalValue = finalValue + FormUpdater.getValue(contents, fields[x]);
                }
                form.setField(key, finalValue);
                continue;
            }
            if (XMLField.indexOf("+") != -1) {
                System.out.println("Adding/Subtracting");
                double finalValue = 0.0;
                String[] fields = XMLField.split("\\+");
                for (int x = 0; x < fields.length; ++x) {
                    System.out.println("Field " + String.valueOf(x) + ": " + fields[x]);
                    boolean isSub = false;
                    if (fields[x].startsWith("-")) {
                        isSub = true;
                        fields[x] = fields[x].substring(1);
                    }
                    if (!isSub) {
                        finalValue+=Double.parseDouble(FormUpdater.getValue(contents, fields[x]));
                        continue;
                    }
                    finalValue-=Double.parseDouble(FormUpdater.getValue(contents, fields[x]));
                }
                form.setField(key, String.valueOf(finalValue));
                continue;
            }
            String XMLFieldValue = FormUpdater.getValue(contents, XMLField);
            form.setField(key, XMLFieldValue);
        }
        filledOutForm.close();
    }

    public static String getValue(StringBuffer contents, String XMLField) {
        int start = contents.indexOf("<" + XMLField + ">") + XMLField.length() + 2;
        int end = contents.indexOf("</" + XMLField + ">");
        System.out.println("Start: " + String.valueOf(start));
        System.out.println("End: " + String.valueOf(end));
        String XMLFieldValue = contents.substring(start, end);
        System.out.println("XMLFieldValue: " + XMLFieldValue);
        return XMLFieldValue;
    }
}

