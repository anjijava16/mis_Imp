Today I did research on flashattributes in spring... I want to share that knowledge to you..
smile emoticon
Assume you have 2 controllers.... If you redirect from one controller to
another controller the values in model object won't be available in the
other controller.... So if you want to share the model object values
then you have to say in first controller
addFlashAttribute("modelkey", "modelvalue");
Then second controller's model contains now the above key value pair..
Second question ?
What is difference between addAttribute and addFlashAttribute....
in RedirectAttributes class
addFlashAttribute already covered above
addAttribute will pass the values as requestparameters so when you add
some using addAttribute you can access those values from request.getParameter....
Here is the code.. to find out what is going on...
smile emoticon
@RequestMapping(value = "/rm1", method = RequestMethod.POST)
public String rm1(Model model,RedirectAttributes rm) {
System.out.println("Entered rm1 method ");
rm.addFlashAttribute("modelkey", "modelvalue");
rm.addAttribute("nonflash", "nonflashvalue");
model.addAttribute("modelkey", "modelvalue");
return "redirect:/rm2.htm";
}
@RequestMapping(value = "/rm2", method = RequestMethod.GET)
public String rm2(Model model,HttpServletRequest request) {
System.out.println("Entered rm2 method ");
Map md = model.asMap();
for (Object modelKey : md.keySet()) {
Object modelValue = md.get(modelKey);
System.out.println(modelKey + " -- " + modelValue);
}
System.out.println("=== Request data ===");
java.util.Enumeration<String> reqEnum = request.getParameterNames();
while (reqEnum.hasMoreElements()) {
String s = reqEnum.nextElement();
System.out.println(s);
System.out.println("==" + request.getParameter(s));
}
return "controller2output";
}
===============================
What happens when you add some thing to Model object..... ?
where it will get stored... ?
If you add 
model.addAttribute("modelkey", "modelvalue");
After the controller method is completed internally spring adds that key
value pair as request attribute.....
If you want to check... you can iterate requestattributes and see...
add this and you can find out
java.util.Enumeration<String> reqEnum = request.getAttributeNames();
while (reqEnum.hasMoreElements()) {
String s = reqEnum.nextElement();
System.out.println(s);
System.out.println("==" + request.getAttribute(s));
}