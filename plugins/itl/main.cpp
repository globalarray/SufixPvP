#include <iostream>
#include <array>
#include <ctime>
#include "color.h"
#include "HTTPRequest.hpp"

using namespace std;

const char* types[4] = {"killaura", "reach", "movement", "eat"};
int status = 0;
double walkingSpeed = 4.3;
int legitEatings = 7;
double maxDiff = 0.0625;
double maxHitDistance = 7.9;

extern "C" double getVersion() {
   return 1.3;
}


string encryption(string toEncrypt) {
    char key[7] = {'K', 'C', 'Q', 'Z', 'F', 'a', 's'};
    string output = toEncrypt;
    
    for (int i = 0; i < toEncrypt.size(); i++)
        output[i] = toEncrypt[i] ^ key[i % (sizeof(key[0]) / sizeof(char))];
    
    return output;
}

extern "C" void sendlog(int type, const char* text) {
   time_t now = time(0);
   tm *ltm = localtime(&now);
   switch(type) {
      case -1: //critical
      printf("%d/%d/%d %d:%d:%d %sCRITICAL%s -> %s\n", 1900 + ltm->tm_year, 1 + ltm->tm_mon, ltm->tm_mday, ltm->tm_hour, ltm->tm_min, ltm->tm_sec, RED, RESET, text);
      exit(1);
      break;
      case 1: //info
      printf("%d/%d/%d %d:%d:%d %sINFO%s -> %s\n", 1900 + ltm->tm_year, 1 + ltm->tm_mon, ltm->tm_mday, ltm->tm_hour, ltm->tm_min, ltm->tm_sec, GREEN, RESET, text);
      break;
      case 2: //notice
      printf("%d/%d/%d %d:%d:%d %sNOTICE%s -> %s\n", 1900 + ltm->tm_year, 1 + ltm->tm_mon, ltm->tm_mday, ltm->tm_hour, ltm->tm_min, ltm->tm_sec, AQUA, RESET, text);
      break;
      case 3: //warning
      printf("%d/%d/%d %d:%d:%d %sWARNING%s -> %s\n", 1900 + ltm->tm_year, 1 + ltm->tm_mon, ltm->tm_mday, ltm->tm_hour, ltm->tm_min, ltm->tm_sec, YELLOW, RESET, text);
      break;
      default:
      //empty type
      break;
   }
}

void isStarting() {
   if (status < 1) {
      sendlog(-1, "SufixLibrary not started, please use function starting");
   }
}

extern "C" const char** getTypes() {
   isStarting();
   return types;
}

extern "C" int getCountTypes() {
   isStarting();
   return sizeof(types) / sizeof(types[0]);
}

extern "C" void starting() {
   sendlog(2, "Sufix Library loading...");
   try {
   http::Request request("http://sufixpvp.su/license");
   const http::Response resp = request.send("GET", "ipv4", {
      {"User-Agent", "RootiTeam / Rooti.ru (#ad3fd4z)"}
   });
   string body = std::string{resp.body.begin(), resp.body.end()};
   if (encryption(body) != "?9*3*>&r8$") {
      sendlog(-1, "Access denied.");
   }

   } catch (const std::exception& e) {
      sendlog(-1, "Sufix Library have much problems for loading...");
   }
   status = 1;
   sendlog(1, "Sufix Library successful loaded.");
}

int main(int argc, char* argv[]) {
   starting();
}
