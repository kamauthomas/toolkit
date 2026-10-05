#!/usr/bin/env node
// Official metadata examples exercise the shared browser formatter without DOM/network.
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const root = process.argv[2] || path.resolve(__dirname, '../../wp-content/themes/eduma-child/assets/js');
const context = {window: {}, document: {readyState: 'complete', querySelectorAll: () => []}};
vm.createContext(context);
for (const file of ['phone-countries.js','phone-input.js']) vm.runInContext(fs.readFileSync(path.join(root,file),'utf8'),context);
const phone = context.window.ToolkitPhone;
let checks = 0;
for (const [region, metadata] of Object.entries(context.window.ToolkitPhoneCountries)) {
    if (!metadata.example) continue;
    const expected = '+' + metadata.code + metadata.example;
    assert.strictEqual(phone.normalize(metadata.example,region),expected,region);
    assert.strictEqual(phone.normalize(expected,region),expected,region+' round trip');
    checks += 2;
}
for (const [country,raw,expected] of [['KE','0712345678','+254712345678'],['KE','0112345678','+254112345678'],['GB','07400123456','+447400123456'],['US','2015550123','+12015550123'],['IT','0212345678','+390212345678']]) {
    assert.strictEqual(phone.normalize(raw,country),expected);checks++;
}
for (const [country,raw] of [['KE','+447400123456'],['INVALID','0712345678'],['GB','123456789012345678'],['KE','0712345678abc']]) {assert.strictEqual(phone.normalize(raw,country),null);checks++;}
assert.strictEqual(phone.countryFor('+447400123456','KE'),'GB');checks++;
for (const [raw,expected] of [['254712345678','+254712345678'],['2540712345678','+254712345678'],['+2540712345678','+254712345678'],['254 712 345 678','+254712345678']]) {assert.strictEqual(phone.normalize(raw,'KE'),expected);checks++;}
for (const [raw,country,expected] of [['447400123456','GB','+447400123456'],['4407400123456','GB','+447400123456'],['12015550123','US','+12015550123'],['2560772123456','UG','+256772123456'],['390212345678','IT','+390212345678']]) {
    assert.strictEqual(phone.countryFor(raw,'KE'),country);assert.strictEqual(phone.normalize(raw,country),expected);checks+=2;
}
for (const [region,metadata] of Object.entries(context.window.ToolkitPhoneCountries)) {assert.strictEqual(metadata.flag,String.fromCodePoint(...[...region].map(c=>0x1F1E6+c.charCodeAt(0)-65)));checks++;}
console.log(`PASS: ${checks} browser formatter checks; 245 official country examples and international round trips`);
