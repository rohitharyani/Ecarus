#include <ArduinoJson.h>
#include <HID.h>
#include <hid.h>
#include <hiduniversal.h>
#include <LiquidCrystal.h>
#include <avr/pgmspace.h>
#include <Usb.h>
#include <usbhub.h>
#include <avr/pgmspace.h>
#include <hidboot.h>
#include <UTFT.h>

// Declare which fonts we will be using
extern uint8_t BigFont[];
static String removeBarcode = "10011001";
static String addBarcode = "10161016";
static String checkBarcode = "30033003";
static String payBarcode = "30193019";
#define DISPLAY_WIDTH 480

UTFT myGLCD(CTE32HR,38,39,40,41);   
USB     Usb;
USBHub     Hub(&Usb);
HIDUniversal  Hid(&Usb);
String barcode;
boolean state = false;
 
class KbdRptParser : public KeyboardReportParser
{
protected:
  virtual void OnKeyDown  (uint8_t mod, uint8_t key);
  virtual void OnKeyPressed(uint8_t key);
};
 
void KbdRptParser::OnKeyDown(uint8_t mod, uint8_t key)  
{
    uint8_t c = OemToAscii(mod, key);
 
    if (c)
        OnKeyPressed(c);
        
}
 
/* what to do when symbol arrives */
void KbdRptParser::OnKeyPressed(uint8_t key)  
{
    barcode = barcode + (char)key;
    Serial.println(barcode);

};
 
KbdRptParser Prs;
static String phone = "7875987888";

#define SSID        "Rohit's Redmi" //TERA SSID NAME DAAL YAHA
#define PASS        "1haryani" //PASSWORD
#define URL "192.168.43.51"
#define PORT  80
#define MAX_SERVER_CONNECT_ATTEMPTS 5

char buffer[1024];
char serverData[1024];
String data="";

char OKrn[] = "OK\r\n";

byte wait_for_esp_response(int timeout, char* term=OKrn) {
  
  unsigned long t=millis();
  bool found=false;
  int i=0;
  int len=strlen(term);

  while(millis()<t+timeout) {
    if(Serial1.available()) {
      buffer[i++]=Serial1.read();
      if(i>=len) {
        if(strncmp(buffer+i-len, term, len)==0) {
          found=true;
          break;
        }
      }
    }
  }
  buffer[i]=0;
  Serial.println(buffer);
  return found;
}

static String userPhone;
String userPhone1="",firstProduct,productId,productId1="";
String productName,productName1,mfgDate,expDate,cost,weight,quantity,productBarcode,message;
String bill,total,discount,subTotal,vat,payable,payable1;
String error,error1;

byte wait_for_server_response(int timeout, char* term=OKrn) {
unsigned long t=millis();
  bool found=false;
  int i=0;
  int len=strlen(term);
  String data="";
  String json="";
  
  while(millis()<t+timeout) {
    if(Serial1.available()) {
      String data = Serial1.readStringUntil('\r');
      if (!found && data.charAt(1) == '{') {
        found = true;
      }
      if(found){
        json+=data;
      }
    }
  }
 Serial.print(json);
 StaticJsonBuffer<400> jsonBuffer;
 JsonObject& jObject = jsonBuffer.parseObject(json);
 String phone = jObject["phone"];
 userPhone=phone;
 userPhone1=phone;
 String mProductId = jObject["productId"];
 productId =mProductId;
 productId1=mProductId;
 //String first = jObject["first"];
// firstProduct = first;
 String mProductName = jObject["productName"];
 productName = mProductName;
 productName1 = mProductName;
 String mMfgDate = jObject["mfgDate"];
 mfgDate = mMfgDate;
 String mExpDate = jObject["expDate"];
 expDate = mExpDate;
 String mCost = jObject["cost"];
 cost=mCost;
 String mWeight = jObject["weight"];
 weight=mWeight;
 String mQuantity = jObject["quantity"];
 quantity = mQuantity;
 String mProductBarcode = jObject["barcode"];
 productBarcode = mProductBarcode;
 
   String mMessage = jObject["message"];
   message = mMessage;
    String mError = jObject["error"];
   error = mError;
   error1 = mError;
   
  String mBill = jObject["bill"];
  bill = mBill;
  String mTotal = jObject["total"];
  total = mTotal;
  String mDiscount = jObject["discount"];
  discount = mDiscount;
  String mSubTotal = jObject["subTotal"];
  subTotal = mSubTotal;
  String mVat = jObject["vat"];
  vat = mVat;
  String mPayable = jObject["payable"];
  payable = mPayable;
  payable1=mPayable;
  
 Serial.print(userPhone); 
 Serial.print(productName); 
 Serial.print(message);
}


byte StartModule()
{
  
  bool module_responding =  false;
  bool connected_to_access_point = false; 
  Serial.println("Starting module");

  while(!module_responding){
  
    Serial1.println("AT+RST");
    
  
    if (wait_for_esp_response(5000, "OK")){
      Serial.println("Module is responding");
      module_responding = true;
    }
    else{
      Serial.println("Module not responding to reset");
      delay(1000);
    }
  }
    
  Serial1.println("AT+CWMODE=1");
  wait_for_esp_response(1000);
  
  Serial1.println("AT+CIPMUX=0");
  wait_for_esp_response(1000);
  
  Serial.println(F("Connecting to WiFi access point..."));
  
  String cmd = "AT+CWJAP=\"";
  cmd += SSID;
  cmd += "\",\"";
  cmd += PASS;
  cmd += "\"";
  
  Serial.println(cmd);
  Serial1.println(cmd);
  
  connected_to_access_point = wait_for_esp_response(9000,"WIFI CONNECTED");
  
  if(!connected_to_access_point){
    Serial.println(F("Attempt to connect to access point failed. Restarting module."));
    return false; 
  }
  else
  {
    Serial.println(F("CONNECTED TO ACCESS POINT"));
  }
}
void setup()
{
  Serial.begin(115200);
  Serial1.begin(115200);

    Serial.println("Start");  
    if (Usb.Init() == -1) {
        Serial.println("OSC did not start.");
    }
 
    delay( 200 );
 
    Hid.SetReportParser(0, (HIDReportParser*)&Prs);
    
    randomSeed(analogRead(0));
  
  // Setup the LCD
    myGLCD.InitLCD();
    myGLCD.setFont(BigFont);

    //*********Display welcomeScreen***********
    welcomeScreen();    
}
  
void loop()
{
  
  if(barcode.length() == 10 && state){
    String cmd="AT+CIPSTART=\"TCP\",\"";
    cmd+=URL;
    cmd+="\",";
    cmd+=PORT;
    Serial1.println(cmd);
    wait_for_esp_response(9000,"CONNECTED");
   
    Serial1.println("AT+CIPSEND=91");
    wait_for_esp_response(9000,"> ");

    Serial1.print("GET /ara/arduino/barcodeGetDetails.php?barcode="+barcode+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
     wait_for_server_response(9000,"Data Received\n");
    barcode="";
     delay(5000);
    
  }
  else if(barcode.length() == 10){
    if(!StartModule()){
    delay(1000);
    Serial.println(F("***Calling StartModule Again***"));
    }

    String cmd="AT+CIPSTART=\"TCP\",\"";
    cmd+=URL;
    cmd+="\",";
    cmd+=PORT;
    Serial1.println(cmd);
    wait_for_esp_response(9000,"CONNECTED");
   
    Serial1.println("AT+CIPSEND=91");
    wait_for_esp_response(9000,"> ");

    Serial1.print("GET /ara/arduino/barcodeGetDetails.php?barcode="+barcode+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
     wait_for_server_response(9000,"Data Received\n");
    barcode="";
     delay(5000);
    
  }
  else{
    Usb.Task();    
  }

  if(userPhone1.length() == 10){
    userPhone1 ="";
    state = true;
    listGetFirstProduct();  
  }

  if(productName1.length() >3 && productId1.length() == 0){
    productName1 ="";
    loadListScreen();  
  }

  if(productId1.length() == 4){
    productId1="";
    loadListScreen();
  }

  if(payable1.length() > 1){
    payable1 = "";
    checkoutScreen();
  }

  if(error1.length() > 3){
    error1 = "";
    errorScreen();
    delay(5000);
    if(state){
      listGetFirstProduct();
    }
    else{
      welcomeScreen();
    }
  }
  
  if(barcode.length() == 8 && barcode.equals(addBarcode)){
    barcode ="";
    addProduct();
  }

  if(barcode.length() == 8 && barcode.equals(removeBarcode)){
    barcode ="";
    removeProduct();
  }

  if(barcode.length() == 8 && barcode.equals(checkBarcode)){
    barcode ="";
    checkout();
  }

  if(barcode.length() == 8 && barcode.equals(payBarcode)){
    state = false;
    barcode ="";
    pay();
  }
  
}

void listGetFirstProduct(){
  String cmd="AT+CIPSTART=\"TCP\",\"";
  cmd+=URL;
  cmd+="\",";
  cmd+=PORT;
  Serial1.println(cmd);  
      
  // Serial1.println( AT+CIPSTART="TCP", "192.168.43.138", 80);
  wait_for_esp_response(9000,"CONNECTED");
     
  Serial1.println("AT+CIPSEND=91");
  wait_for_esp_response(9000,"> ");
  
  Serial1.print("GET /ara/arduino/listGetFirstProduct.php?phone="+phone+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
  wait_for_server_response(9000,"Data Received\n");
  delay(1000);
  
}

void addProduct(){
  Serial.print("\n\n");
  Serial.print(productId);
  Serial.print("\n\n");
  Serial.print(userPhone);
  String cmd="AT+CIPSTART=\"TCP\",\"";
  cmd+=URL;
  cmd+="\",";
  cmd+=PORT;
  Serial1.println(cmd);  
      
  // Serial1.println( AT+CIPSTART="TCP", "192.168.43.138", 80);
  wait_for_esp_response(9000,"CONNECTED");
     
  Serial1.println("AT+CIPSEND=101");
  wait_for_esp_response(9000,"> ");
  
  Serial1.print("GET /ara/arduino/cartAddProduct.php?phone="+phone+"&productId="+productId+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
  wait_for_server_response(9000,"Data Received\n");
  delay(10000);
  loadListScreen();
  delay(3000);
  listGetFirstProduct();  
}

void removeProduct(){
  String cmd="AT+CIPSTART=\"TCP\",\"";
  cmd+=URL;
  cmd+="\",";
  cmd+=PORT;
  Serial1.println(cmd);  
      
  // Serial1.println( AT+CIPSTART="TCP", "192.168.43.138", 80);
  wait_for_esp_response(9000,"CONNECTED");
     
  Serial1.println("AT+CIPSEND=104");
  wait_for_esp_response(9000,"> ");
  
  Serial1.print("GET /ara/arduino/cartRemoveProduct.php?phone="+phone+"&productId="+productId+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
  wait_for_server_response(9000,"Data Received\n");
  delay(1000);
  
  loadListScreen();
  delay(3000);
  listGetFirstProduct();
}

void checkout(){
  String cmd="AT+CIPSTART=\"TCP\",\"";
  cmd+=URL;
  cmd+="\",";
  cmd+=PORT;
  Serial1.println(cmd);  
      
  // Serial1.println( AT+CIPSTART="TCP", "192.168.43.138", 80);
  wait_for_esp_response(9000,"CONNECTED");
     
  Serial1.println("AT+CIPSEND=83");
  wait_for_esp_response(9000,"> ");
  
  Serial1.print("GET /ara/arduino/cartGetBill.php?phone="+phone+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
  wait_for_server_response(9000,"Data Received\n");
  delay(1000);
  
}

void pay(){
  String cmd="AT+CIPSTART=\"TCP\",\"";
  cmd+=URL;
  cmd+="\",";
  cmd+=PORT;
  Serial1.println(cmd);  
      
  // Serial1.println( AT+CIPSTART="TCP", "192.168.43.138", 80);
  wait_for_esp_response(9000,"CONNECTED");
     
  Serial1.println("AT+CIPSEND=94");
  wait_for_esp_response(9000,"> ");
  
  Serial1.print("GET /ara/arduino/walletCheckAndCheckout.php?phone="+phone+" HTTP/1.1\r\nHost: 192.168.43.51\r\n\r\n");
  wait_for_server_response(9000,"Data Received\n");
  delay(1000);
  
}

void welcomeScreen(){
    myGLCD.clrScr();

  //Top bar color and filling
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(0, 0, 479, 39);

  //start button
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("ARA - An Ingenious Pushcart", CENTER, 10);

  //Start message1
  myGLCD.setColor(0, 153, 204);
  myGLCD.fillRect(0, 39, 479, 319);
  myGLCD.setColor(255,255,255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Welcome to ARA!", CENTER, 119);

  //start message2
  myGLCD.setColor(255,255,255);
  myGLCD.setBackColor(0, 153, 194);
  myGLCD.print("Please scan your membership", CENTER, 200);
  myGLCD.setColor(255,255,255);
  myGLCD.setBackColor(0, 153, 194);
  myGLCD.print("card to continue shopping", CENTER, 230);
}

void loadListScreen(){
  myGLCD.clrScr();

  //Top bar color and filling
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(0, 0, 479, 39);

  //Checkout button
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("Checkout", LEFT, 10);

  //checkout button
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("ARA", RIGHT, 10);
 
//Top pane - show product name
  myGLCD.setColor(195, 195, 195);
  myGLCD.fillRect(0, 39, 479, 79);

//-----------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(195, 195, 195);
  myGLCD.print(productName, 0, 50);

//-----------------------------------------------

 //Middle pane - show product
  myGLCD.setColor(255, 255, 255);
  myGLCD.fillRect(0, 79, 479, 279);

  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print("MFG DATE:", 20, 110);

  //-------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(mfgDate, 180, 110);
  //-------------------------------------

  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print("EXP DATE:", 20, 155);

  //-------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(expDate, 180, 155);
  //-------------------------------------

  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print("COST /Kg:", 20, 200);
  //-------------------------------------
  
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(cost, 180, 200);
  //-------------------------------------

  
  //--------------------------------------

  //Botom pane - note
  myGLCD.setColor(195, 195, 195);
  myGLCD.fillRect(0, 240, 479, 279);

//-----------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(195, 195, 195);
  myGLCD.print("Note:", 20, 250);

  //Display message - note
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(195, 195, 195);
  myGLCD.print(message, 100, 250);

//-----------------------------------------------

   
  //Last pane - remove product
  //Left side - last pane
  myGLCD.setColor(255,255,255);
  myGLCD.fillRect(0, 279, 239, 319);

  //Remove button
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print("REMOVE", 50, 290);

  //Right side - last pane
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(239, 279, 479, 319);

  //Add button
  myGLCD.setColor(255,255,255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("ADD", 340, 290);

}

void checkoutScreen(){
  myGLCD.clrScr();

  //Top bar color
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(0, 0, 479, 39);

  //ARA tag 
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("ARA", LEFT, 10);

  //Pay button
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("PAY", RIGHT, 10);

  //Left pane - bill functions
  myGLCD.setColor(0, 153, 204);
  myGLCD.fillRect(0, 39, 239, 279);

//Right pane - bill details
  myGLCD.setColor(255, 255, 255);
  myGLCD.fillRect(239, 39, 479, 279);
  
  //Cart Total tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Cart Total:", LEFT, 60);
//Remaining to Print actual value of cart total
//------------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(bill, 250, 60);
//------------------------------------------------


  //Total items tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Total Items:", LEFT, 95);
//Remaining to Print actual value of total items
//---------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(total, 250, 95);
//---------------------------------------------
  
  //Cart Discount tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Cart Discount:", LEFT, 130);
//Remaining to Print actual value of cart discount
//--------------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(discount, 250, 130);
//----------------------------------------------------
  
  
  //Sub total tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Sub Total:", LEFT, 165);
//Remaining to Print actual value of sub total
//-----------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(subTotal, 250, 165);
//-----------------------------------------------
  
  //Vat tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("VAT (5.4%):", LEFT, 200);
//Remaining to Print actual value of VAT
//----------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(vat, 250, 200);
//-----------------------------------------
  
  //Total payable tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(0, 153, 204);
  myGLCD.print("Total Payable:", LEFT, 235);
  //Remaining to Print actual value of total payable
//--------------------------------------------------
  myGLCD.setColor(0,0,0);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(payable, 250, 235);
//--------------------------------------------------

  //Bottom pane - total payable
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(0, 279, 479, 319);
  
  //Billing for tag
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("Billing for:", LEFT, 290);
  //Remaining to Print actual value of total payable
//--------------------------------------------------
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print(phone, 250, 290);
//--------------------------------------------------
 
}

void errorScreen(){
    myGLCD.clrScr();

  //Top bar color and filling
  myGLCD.setColor(51, 181, 229);
  myGLCD.fillRect(0, 0, 479, 39);

  //start button
  myGLCD.setColor(255, 255, 255);
  myGLCD.setBackColor(51, 181, 229);
  myGLCD.print("ARA - An Ingenious Pushcart", CENTER, 10);

  myGLCD.setColor(0, 153, 204);
  myGLCD.fillRect(0, 39, 479, 319);
  
  //Start message1
  myGLCD.setColor(255,255,255);
  myGLCD.fillRect(20,95,459,149);
  myGLCD.setColor(0, 153, 204);
  myGLCD.setBackColor(255,255,255);
  myGLCD.print(message, CENTER, 115);

}



