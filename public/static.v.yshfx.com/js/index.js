
$(function () {
  chart();
});
function chart() {
  var items = [{ "id": 13152, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "0.83", "loss": null, "op": "1", "dlen": "5", "open_price": "0.74706", "close_price": "0.74694", "sell_price": null, "buy_price": null, "profit": "9.96", "commission": "", "comment": null, "swap": "-11.81", "takeprofit": "0", "stoploss": "0.75250", "open_time": 1493222267, "close_time": 1493364837, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13151, "account": "2089568423", "ticket": null, "symbol": "GBPUSD", "lots": "0.28", "loss": null, "op": "0", "dlen": "5", "open_price": "1.28332", "close_price": "1.29157", "sell_price": null, "buy_price": null, "profit": "231.00", "commission": "", "comment": null, "swap": "-28.34", "takeprofit": "1.29950", "stoploss": "1.28350", "open_time": 1492553888, "close_time": 1493584072, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13150, "account": "2089568423", "ticket": null, "symbol": "EURUSD", "lots": "1.00", "loss": null, "op": "0", "dlen": "5", "open_price": "1.09869", "close_price": "1.12267", "sell_price": null, "buy_price": null, "profit": "2398.00", "commission": "", "comment": null, "swap": "-46.83", "takeprofit": "0", "stoploss": "1.10500", "open_time": 1494876909, "close_time": 1495529911, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13149, "account": "2089568423", "ticket": null, "symbol": "EURUSD", "lots": "0.50", "loss": null, "op": "0", "dlen": "5", "open_price": "1.09869", "close_price": "1.11887", "sell_price": null, "buy_price": null, "profit": "1009.00", "commission": "", "comment": null, "swap": "-26.76", "takeprofit": "0", "stoploss": "1.10500", "open_time": 1494876909, "close_time": 1495574947, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13148, "account": "2089568423", "ticket": null, "symbol": "EURUSD", "lots": "0.50", "loss": null, "op": "0", "dlen": "5", "open_price": "1.09869", "close_price": "1.11804", "sell_price": null, "buy_price": null, "profit": "967.50", "commission": "", "comment": null, "swap": "-26.76", "takeprofit": "0", "stoploss": "1.10500", "open_time": 1494876909, "close_time": 1495578008, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13147, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.00", "loss": null, "op": "1", "dlen": "5", "open_price": "0.74497", "close_price": "0.74123", "sell_price": null, "buy_price": null, "profit": "374.00", "commission": "", "comment": null, "swap": "-39.16", "takeprofit": "0", "stoploss": "0.75200", "open_time": 1495578056, "close_time": 1496391018, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13146, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.00", "loss": null, "op": "1", "dlen": "5", "open_price": "0.74501", "close_price": "0.74116", "sell_price": null, "buy_price": null, "profit": "385.00", "commission": "", "comment": null, "swap": "-39.16", "takeprofit": "0", "stoploss": "0.75200", "open_time": 1495578068, "close_time": 1496391068, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13145, "account": "2089568423", "ticket": null, "symbol": "EURNZD", "lots": "1.00", "loss": null, "op": "1", "dlen": "5", "open_price": "1.56979", "close_price": "1.55800", "sell_price": null, "buy_price": null, "profit": "848.23", "commission": "", "comment": null, "swap": "0", "takeprofit": "1.55800", "stoploss": "1.58120", "open_time": 1496781223, "close_time": 1496815223, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13144, "account": "2089568423", "ticket": null, "symbol": "NZDCAD", "lots": "0.50", "loss": null, "op": "0", "dlen": "5", "open_price": "0.96219", "close_price": "0.97310", "sell_price": null, "buy_price": null, "profit": "403.43", "commission": "", "comment": null, "swap": "0.54", "takeprofit": "0.97310", "stoploss": "0.95390", "open_time": 1496612841, "close_time": 1496835627, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13143, "account": "2089568423", "ticket": null, "symbol": "CADCHF", "lots": "1.00", "loss": null, "op": "0", "dlen": "5", "open_price": "0.73013", "close_price": "0.73373", "sell_price": null, "buy_price": null, "profit": "369.12", "commission": "", "comment": null, "swap": "2.23", "takeprofit": "0", "stoploss": "0.71920", "open_time": 1497300438, "close_time": 1497526275, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13142, "account": "2089568423", "ticket": null, "symbol": "NZDJPY", "lots": "1.00", "loss": null, "op": "0", "dlen": "3", "open_price": "79.580", "close_price": "80.302", "sell_price": null, "buy_price": null, "profit": "652.42", "commission": "", "comment": null, "swap": "10.51", "takeprofit": "0", "stoploss": "79.600", "open_time": 1496964512, "close_time": 1497607158, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13141, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.00", "loss": null, "op": "0", "dlen": "5", "open_price": "0.76228", "close_price": "0.75635", "sell_price": null, "buy_price": null, "profit": "-593.00", "commission": "", "comment": null, "swap": "0.52", "takeprofit": "0", "stoploss": "0.75640", "open_time": 1497808460, "close_time": 1497987014, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13140, "account": "2089568423", "ticket": null, "symbol": "NZDJPY", "lots": "1.00", "loss": null, "op": "0", "dlen": "3", "open_price": "80.896", "close_price": "81.900", "sell_price": null, "buy_price": null, "profit": "893.83", "commission": "", "comment": null, "swap": "17.72", "takeprofit": "0", "stoploss": "81.900", "open_time": 1497816748, "close_time": 1498733808, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13139, "account": "2089568423", "ticket": null, "symbol": "USDJPY", "lots": "1.00", "loss": null, "op": "0", "dlen": "3", "open_price": "112.010", "close_price": "112.180", "sell_price": null, "buy_price": null, "profit": "151.54", "commission": "", "comment": null, "swap": "-3.96", "takeprofit": "0", "stoploss": "112.180", "open_time": 1498510450, "close_time": 1498735736, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13138, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.50", "loss": null, "op": "0", "dlen": "5", "open_price": "0.76489", "close_price": "0.76990", "sell_price": null, "buy_price": null, "profit": "751.50", "commission": "", "comment": null, "swap": "0.39", "takeprofit": "0", "stoploss": "0.75700", "open_time": 1498679672, "close_time": 1498773716, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13137, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "1.36", "loss": null, "op": "1", "dlen": "5", "open_price": "0.99155", "close_price": "0.98553", "sell_price": null, "buy_price": null, "profit": "631.25", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "0.99138", "open_time": 1499125035, "close_time": 1499156975, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13136, "account": "2089568423", "ticket": null, "symbol": "GBPCHF", "lots": "1.44", "loss": null, "op": "0", "dlen": "5", "open_price": "1.24794", "close_price": "1.24694", "sell_price": null, "buy_price": null, "profit": "-149.20", "commission": "", "comment": null, "swap": "-2.14", "takeprofit": "0", "stoploss": "1.23890", "open_time": 1499030600, "close_time": 1499165180, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13135, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "1.23", "loss": null, "op": "1", "dlen": "5", "open_price": "0.99155", "close_price": "0.98563", "sell_price": null, "buy_price": null, "profit": "562.74", "commission": "", "comment": null, "swap": "-5.30", "takeprofit": "0", "stoploss": "0.99138", "open_time": 1499125035, "close_time": 1499201911, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13134, "account": "2089568423", "ticket": null, "symbol": "EURAUD", "lots": "1.50", "loss": null, "op": "0", "dlen": "5", "open_price": "1.50287", "close_price": "1.50050", "sell_price": null, "buy_price": null, "profit": "-271.49", "commission": "", "comment": null, "swap": "-59.82", "takeprofit": "1.51500", "stoploss": "1.49000", "open_time": 1499334212, "close_time": 1499789583, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13133, "account": "2089568423", "ticket": null, "symbol": "AUDNZD", "lots": "2.61", "loss": null, "op": "0", "dlen": "5", "open_price": "1.05177", "close_price": "1.05627", "sell_price": null, "buy_price": null, "profit": "848.81", "commission": "", "comment": null, "swap": "-9.67", "takeprofit": "0", "stoploss": "1.05190", "open_time": 1499724272, "close_time": 1499789595, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13132, "account": "2089568423", "ticket": null, "symbol": "GBPCAD", "lots": "1.27", "loss": null, "op": "1", "dlen": "5", "open_price": "1.65280", "close_price": "1.65329", "sell_price": null, "buy_price": null, "profit": "-48.52", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.66810", "open_time": 1499850448, "close_time": 1499851189, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13131, "account": "2089568423", "ticket": null, "symbol": "CADCHF", "lots": "0.46", "loss": null, "op": "0", "dlen": "5", "open_price": "0.75026", "close_price": "0.75634", "sell_price": null, "buy_price": null, "profit": "290.26", "commission": "", "comment": null, "swap": "0.77", "takeprofit": "0", "stoploss": "0.75030", "open_time": 1499850300, "close_time": 1499900101, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13130, "account": "2089568423", "ticket": null, "symbol": "CADCHF", "lots": "2.00", "loss": null, "op": "0", "dlen": "5", "open_price": "0.75077", "close_price": "0.75634", "sell_price": null, "buy_price": null, "profit": "1156.12", "commission": "", "comment": null, "swap": "3.36", "takeprofit": "0", "stoploss": "0.75090", "open_time": 1499850341, "close_time": 1499900107, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, {
    "id": 13129, "account": "2089568423", "ticket": null, "symbol": "EURGBP", "lots": "0.93", "loss": null, "op": "0", "dlen": "5", "open_price": "0.89366", "close_price": "0.88392", "sell_price": null, "buy_price": null, "profit": "-1172.56", "commission": "", "comment": null, "swap": "-10.10", "takeprofit": "0", "stoploss": "0.88400", "open_time": 1499810803, "close_time": 1499911227, "operator": "import-api", "add_time": 1507703055, "modify_time":
    1507703055
  }, { "id": 13128, "account": "2089568423", "ticket": null, "symbol": "AUDNZD", "lots": "2.52", "loss": null, "op": "0", "dlen": "5", "open_price": "1.05835", "close_price": "1.05419", "sell_price": null, "buy_price": null, "profit": "-769.18", "commission": "", "comment": null, "swap": "-28.14", "takeprofit": "1.06684", "stoploss": "1.05417", "open_time": 1499801442, "close_time": 1499922050, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13127, "account": "2089568423", "ticket": null, "symbol": "XAUUSD", "lots": "1.46", "loss": null, "op": "0", "dlen": "2", "open_price": "1223.35", "close_price": "1226.90", "sell_price": null, "buy_price": null, "profit": "518.30", "commission": "", "comment": null, "swap": "-2.93", "takeprofit": "1234.70", "stoploss": "1224.00", "open_time": 1499902074, "close_time": 1500028825, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13126, "account": "2089568423", "ticket": null, "symbol": "NZDCHF", "lots": "2.24", "loss": null, "op": "1", "dlen": "5", "open_price": "0.69910", "close_price": "0.70152", "sell_price": null, "buy_price": null, "profit": "-565.12", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "0.70560", "open_time": 1500313664, "close_time": 1500325377, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13125, "account": "2089568423", "ticket": null, "symbol": "AUDCHF", "lots": "1.71", "loss": null, "op": "0", "dlen": "5", "open_price": "0.75450", "close_price": "0.75640", "sell_price": null, "buy_price": null, "profit": "340.21", "commission": "", "comment": null, "swap": "4.30", "takeprofit": "0.76318", "stoploss": "0.75470", "open_time": 1500239501, "close_time": 1500363286, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13124, "account": "2089568423", "ticket": null, "symbol": "AUDNZD", "lots": "1.07", "loss": null, "op": "0", "dlen": "5", "open_price": "1.06510", "close_price": "1.07770", "sell_price": null, "buy_price": null, "profit": "990.57", "commission": "", "comment": null, "swap": "-4.02", "takeprofit": "0", "stoploss": "1.06800", "open_time": 1500235221, "close_time": 1500363323, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13123, "account": "2089568423", "ticket": null, "symbol": "AUDNZD", "lots": "0.93", "loss": null, "op": "0", "dlen": "5", "open_price": "1.06510", "close_price": "1.07523", "sell_price": null, "buy_price": null, "profit": "694.13", "commission": "", "comment": null, "swap": "-3.49", "takeprofit": "0", "stoploss": "1.06800", "open_time": 1500235221, "close_time": 1500374475, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13122, "account": "2089568423", "ticket": null, "symbol": "AUDNZD", "lots": "2.61", "loss": null, "op": "0", "dlen": "5", "open_price": "1.08202", "close_price": "1.07944", "sell_price": null, "buy_price": null, "profit": "-494.41", "commission": "", "comment": null, "swap": "0", "takeprofit": "1.09062", "stoploss": "1.07340", "open_time": 1500492384, "close_time": 1500505049, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13121, "account": "2089568423", "ticket": null, "symbol": "XAUUSD", "lots": "1.45", "loss": null, "op": "0", "dlen": "2", "open_price": "1243.45", "close_price": "1237.85", "sell_price": null, "buy_price": null, "profit": "-812.00", "commission": "", "comment": null, "swap": "-8.74", "takeprofit": "1254.80", "stoploss": "1232.00", "open_time": 1500406038, "close_time": 1500505094, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13120, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "2.85", "loss": null, "op": "1", "dlen": "5", "open_price": "1.00036", "close_price": "0.99812", "sell_price": null, "buy_price": null, "profit": "508.13", "commission": "", "comment": null, "swap": "0", "takeprofit": "0.99600", "stoploss": "1.00390", "open_time": 1500504871, "close_time": 1500543340, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13119, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "3.06", "loss": null, "op": "1", "dlen": "5", "open_price": "1.00036", "close_price": "0.99937", "sell_price": null, "buy_price": null, "profit": "241.09", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.00020", "open_time": 1500504871, "close_time": 1500543804, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13118, "account": "2089568423", "ticket": null, "symbol": "EURAUD", "lots": "0.67", "loss": null, "op": "0", "dlen": "5", "open_price": "1.46359", "close_price": "1.47435", "sell_price": null, "buy_price": null, "profit": "568.53", "commission": "", "comment": null, "swap": "-6.99", "takeprofit": "0", "stoploss": "1.46600", "open_time": 1500543683, "close_time": 1500587291, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13117, "account": "2089568423", "ticket": null, "symbol": "EURAUD", "lots": "1.01", "loss": null, "op": "0", "dlen": "5", "open_price": "1.46359", "close_price": "1.46800", "sell_price": null, "buy_price": null, "profit": "353.33", "commission": "", "comment": null, "swap": "-10.53", "takeprofit": "0", "stoploss": "1.46800", "open_time": 1500543683, "close_time": 1500623665, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13116, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "2.82", "loss": null, "op": "1", "dlen": "5", "open_price": "0.98900", "close_price": "0.99650", "sell_price": null, "buy_price": null, "profit": "-1699.11", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "0.99650", "open_time": 1501015199, "close_time": 1501084588, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13115, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.41", "loss": null, "op": "0", "dlen": "5", "open_price": "0.80077", "close_price": "0.79617", "sell_price": null, "buy_price": null, "profit": "-648.60", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "0.79000", "open_time": 1501085525, "close_time": 1501152516, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13114, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "2.77", "loss": null, "op": "0", "dlen": "5", "open_price": "1.00357", "close_price": "0.99700", "sell_price": null, "buy_price": null, "profit": "460.29", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "0.99700", "open_time": 1501544405, "close_time": 1501557979, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13113, "account": "2089568423", "ticket": null, "symbol": "NZDJPY", "lots": "1.60", "loss": null, "op": "1", "dlen": "3", "open_price": "82.091", "close_price": "82.070", "sell_price": null, "buy_price": null, "profit": "30.51", "commission": "", "comment": null, "swap": "-42.33", "takeprofit": "0", "stoploss": "82.070", "open_time": 1501609501, "close_time": 1501830735, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13112, "account": "2089568423", "ticket": null, "symbol": "EURGBP", "lots": "1.06", "loss": null, "op": "0", "dlen": "5", "open_price": "0.89476", "close_price": "0.90400", "sell_price": null, "buy_price": null, "profit": "1269.67", "commission": "", "comment": null, "swap": "-54.75", "takeprofit": "0", "stoploss": "0.90400", "open_time": 1500934232, "close_time": 1502189848, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13111, "account": "2089568423", "ticket": null, "symbol": "NZDJPY", "lots": "1.39", "loss": null, "op": "1", "dlen": "3", "open_price": "80.652", "close_price": "79.411", "sell_price": null, "buy_price": null, "profit": "1581.28", "commission": "", "comment": null, "swap": "-37.01", "takeprofit": "0", "stoploss": "81.780", "open_time": 1502218950, "close_time": 1502392530, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13110, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "2.55", "loss": null, "op": "1", "dlen": "5", "open_price": "0.78598", "close_price": "0.78737", "sell_price": null, "buy_price": null, "profit": "-354.45", "commission": "", "comment": null, "swap": "-36.31", "takeprofit": "0.78070", "stoploss": "0.79160", "open_time": 1502225781, "close_time": 1502392535, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13109, "account": "2089568423", "ticket": null, "symbol": "GBPUSD", "lots": "1.18", "loss": null, "op": "1", "dlen": "5", "open_price": "1.28693", "close_price": "1.28822", "sell_price": null, "buy_price": null, "profit": "-152.22", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.30000", "open_time": 1502816525, "close_time": 1502891268, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13108, "account": "2089568423", "ticket": null, "symbol": "USDMXN", "lots": "1.23", "loss": null, "op": "1", "dlen": "4", "open_price": "17.6783", "close_price": "17.9000", "sell_price": null, "buy_price": null, "profit": "-1523.41", "commission": "", "comment": null, "swap": "29.19", "takeprofit": "17.4568", "stoploss": "17.9000", "open_time": 1502890901, "close_time": 1502995654, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13107, "account": "2089568423", "ticket": null, "symbol": "AUDUSD", "lots": "1.33", "loss": null, "op": "0", "dlen": "5", "open_price": "0.79198", "close_price": "0.79236", "sell_price": null, "buy_price": null, "profit": "50.54", "commission": "", "comment": null, "swap": "1.74", "takeprofit": "0", "stoploss": "0.78040", "open_time": 1502891178, "close_time": 1503278841, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13106, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "1.36", "loss": null, "op": "0", "dlen": "5", "open_price": "1.00373", "close_price": "0.99154", "sell_price": null, "buy_price": null, "profit": "322.27", "commission": "", "comment": null, "swap": "1.95", "takeprofit": "0", "stoploss": "0.99160", "open_time": 1502062663, "close_time": 1503387024, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, {
    "id": 13105, "account": "2089568423", "ticket": null, "symbol": "EURNZD", "lots": "0.73", "loss": null, "op": "0", "dlen": "5", "open_price": "1.62507", "close_price": "1.65003", "sell_price": null, "buy_price": null, "profit": "1322.32", "commission": "", "comment": null, "swap": "-41.33", "takeprofit": "0", "stoploss": "1.62520", "open_time": 1503434941, "close_time": 1503922312, "operator": "import-api", "add_time":
    1507703055, "modify_time": 1507703055
  }, { "id": 13104, "account": "2089568423", "ticket": null, "symbol": "XAUUSD", "lots": "0.78", "loss": null, "op": "0", "dlen": "2", "open_price": "1307.55", "close_price": "1323.37", "sell_price": null, "buy_price": null, "profit": "1233.96", "commission": "", "comment": null, "swap": "-1.57", "takeprofit": "1323.37", "stoploss": "1291.63", "open_time": 1503922122, "close_time": 1503940685, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13103, "account": "2089568423", "ticket": null, "symbol": "EURNZD", "lots": "0.83", "loss": null, "op": "0", "dlen": "5", "open_price": "1.62507", "close_price": "1.64774", "sell_price": null, "buy_price": null, "profit": "1367.48", "commission": "", "comment": null, "swap": "-65.87", "takeprofit": "0", "stoploss": "1.64000", "open_time": 1503434941, "close_time": 1504045014, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13102, "account": "2089568423", "ticket": null, "symbol": "NZDUSD", "lots": "1.82", "loss": null, "op": "1", "dlen": "5", "open_price": "0.71963", "close_price": "0.71953", "sell_price": null, "buy_price": null, "profit": "18.20", "commission": "", "comment": null, "swap": "-36.61", "takeprofit": "0", "stoploss": "0.71940", "open_time": 1504095696, "close_time": 1504251011, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13101, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "2.13", "loss": null, "op": "1", "dlen": "5", "open_price": "0.98602", "close_price": "0.97730", "sell_price": null, "buy_price": null, "profit": "1515.72", "commission": "", "comment": null, "swap": "-19.15", "takeprofit": "0.97730", "stoploss": "0.99550", "open_time": 1504477989, "close_time": 1504688401, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13100, "account": "2089568423", "ticket": null, "symbol": "EURAUD", "lots": "2.31", "loss": null, "op": "0", "dlen": "5", "open_price": "1.49046", "close_price": "1.49070", "sell_price": null, "buy_price": null, "profit": "44.42", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.49070", "open_time": 1504644070, "close_time": 1504695310, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13099, "account": "2089568423", "ticket": null, "symbol": "AUDCAD", "lots": "1.42", "loss": null, "op": "1", "dlen": "5", "open_price": "0.97710", "close_price": "0.97995", "sell_price": null, "buy_price": null, "profit": "-334.10", "commission": "", "comment": null, "swap": "-6.53", "takeprofit": "0", "stoploss": "0.99250", "open_time": 1504732200, "close_time": 1504859359, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13098, "account": "2089568423", "ticket": null, "symbol": "GBPUSD", "lots": "1.98", "loss": null, "op": "0", "dlen": "5", "open_price": "1.31003", "close_price": "1.31900", "sell_price": null, "buy_price": null, "profit": "1776.06", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.31900", "open_time": 1504811743, "close_time": 1504861539, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13097, "account": "2089568423", "ticket": null, "symbol": "GBPCHF", "lots": "0.61", "loss": null, "op": "0", "dlen": "5", "open_price": "1.25927", "close_price": "1.27206", "sell_price": null, "buy_price": null, "profit": "814.03", "commission": "", "comment": null, "swap": "0", "takeprofit": "0", "stoploss": "1.26000", "open_time": 1505153835, "close_time": 1505212063, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13096, "account": "2089568423", "ticket": null, "symbol": "GBPCHF", "lots": "0.20", "loss": null, "op": "0", "dlen": "5", "open_price": "1.25927", "close_price": "1.27665", "sell_price": null, "buy_price": null, "profit": "362.27", "commission": "", "comment": null, "swap": "-0.30", "takeprofit": "0", "stoploss": "1.26200", "open_time": 1505153835, "close_time": 1505260979, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }, { "id": 13095, "account": "2089568423", "ticket": null, "symbol": "GBPCHF", "lots": "0.53", "loss": null, "op": "0", "dlen": "5", "open_price": "1.25927", "close_price": "1.27258", "sell_price": null, "buy_price": null, "profit": "731.90", "commission": "", "comment": null, "swap": "-3.15", "takeprofit": "0", "stoploss": "1.26800", "open_time": 1505153835, "close_time": 1505341312, "operator": "import-api", "add_time": 1507703055, "modify_time": 1507703055 }];
  var d = [];
  d[0] = [0, 0, 0, 0, 0, 0, 0, 0];
  for (var i = 0; i < items.length; i++) {
    var ds = Math.abs(Math.round((parseFloat(items[i].close_price) - parseFloat(items[i].open_price)) * Math.pow(10, items[i].dlen)));
    var type = items[i].op == 1 ? 'sell' : 'buy';
    if (items[i].commission == '') {
      var sxf = 0;
    } else {
      var sxf = items[i].commission;
    }
    if (items[i].swap == null) {
      var kcf = 0;
    } else {
      var kcf = items[i].swap;
    }
    var otime = new Date(parseInt(items[i].open_time * 1000));
    var time = otime.getFullYear() + '-' + (otime.getMonth() + 1) + '-' + otime.getDate();
    d[i + 1] = [i + 1, d[i][1] + parseFloat(items[i].profit) + parseFloat(sxf) + parseFloat(kcf), items[i].symbol, type, items[i].lots, items[i].profit, ds, time];

  }
  setOption(d);
}
function setOption(d) {
  var xy = d;
  var dom = document.getElementById("indexcharts");
  var myChart = echarts.init(dom);
  var option = {
    tooltip: {
      trigger: 'axis',
      triggerOn: 'mousemove',
      formatter: function (params) {
        var paramsNum = params[0].value;
        return '利润：美元' + paramsNum[1].toFixed(2);
      },
      backgroundColor: '#ff8932',
      borderColor: '#ff8932',
      borderWidth: '1',
      textStyle: {
        fontSize: 14,
        color: '#fff'
      }
    },
    grid: {
      left: -5,
      right: -2.3,
      top: -0.2,
      bottom: -36
    },
    xAxis: {
      type: "category",
      name: "",
      scale: true,
      axisLine: {
        onZero: true,
        show: false
      },
      data: xy.map(function (item) {
        return item[0]
      }),
      splitLine: {
        show: false,
        lineStyle: {
          color: "#dfdfdf",
          type: "solid"
        }
      },
      axisTick: {
        show: false,
        lineStyle: {
          color: '#dfdfdf'
        }
      }
    },
    yAxis: [
      {
        type: 'value',
        name: '',
        axisLabel: {
          formatter: '{value} ',
          show: false
        },
        splitLine: {
          lineStyle: {
            color: "#dfdfdf",
            type: "solid"
          }
        },
        axisTick: {
          show: false
        }
      }
    ],
    dataZoom: [{
      type: 'inside',
      start: 0,      /*时间调节放在最后面90%-100%*/
      end: 100
    }, {
      start: 0,
      end: 100,
      show: false,
      handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
      handleSize: '80%',
      handleStyle: {
        color: '#fff',
        shadowBlur: 3,
        shadowColor: 'rgba(0, 0, 0, 0.6)',
        shadowOffsetX: 2,
        shadowOffsetY: 2
      }
    }],
    visualMap: {
      show: false,
      left: 100,
      top: 10,
      pieces: [{
        gt: 0,
        lte: 100000,
        color: '#611987'
      }],
      outOfRange: {
        color: '#611987'
      }
    },
    series: [
      {
        animation: true,
        animationDuration: 300,
        name: '交易',
        type: 'line',
        smooth: true,
        showSymbol: false,
        lineStyle: {
          normal: {
            width: 2
          }
        },
        itemStyle: {
          normal: {
            color: '#611987'
          }
        },
        areaStyle: {
          normal: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
              offset: 0,
              color: '#611987'
            },
            {
              offset: 1,
              color: '#fff'
            }
            ])
          }
        },
        data: xy.map(function (item) {
          return item;
        })/*,
          markLine:
          {
          silent: true,
          lineStyle:{
          normal:{
          type:'solid',
          color:"red",
          width:1.3,
          opacity:0.8
          }
          },
          data:[
          [
          {
          // 起点和终点的项会共用一个 name
          name: '最小值到最大值',
          type: 'min'
          },
          {
          type: 'max'
          }
          ]
          ]
          }*/
      }
    ]
  };
  if (option && typeof option === "object") {
    myChart.setOption(option, true);
    $(".indexload").hide();
  }
}