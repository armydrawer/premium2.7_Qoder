<?php
if (!defined('ABSPATH')) exit();

add_filter('new_parser_links', 'def_new_parser_links');
function def_new_parser_links($links) {

    $time = current_time('timestamp');
    $tomorrow = $time + (24 * 60 * 60);

    $links['cbr'] = array(
        'title' => 'Cbr.ru',
        'url' => 'https://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d.m.Y', $tomorrow),
        'birg_key' => 'cbr',
    );
    $links['moex'] = array(
        'title' => 'Moex.com',
        'url' => 'https://iss.moex.com/iss/engines/currency/markets/selt/boards/CETS/securities/USD000UTSTOM.json',
        'birg_key' => 'moex',
    );
    $links['ecb'] = array(
        'title' => 'Ecb.eu',
        'url' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
        'birg_key' => 'ecb',
    );
    $links['nbu'] = array(
        'title' => 'Nbu',
        'url' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
        'birg_key' => 'nbu',
    );
    $links['privat24'] = array(
        'title' => 'Privatbank Online',
        'url' => 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=11',
        'birg_key' => 'privat24',
    );
    $links['privat'] = array(
        'title' => 'Privatbank',
        'url' => 'https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5',
        'birg_key' => 'privat',
    );
    $links['bankgovua'] = array(
        'title' => 'Bank.gov.ua',
        'url' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
        'birg_key' => 'bankgovua',
    );
    $links['nationalkz'] = array(
        'title' => 'Nationalbank.KZ',
        'url' => 'https://www.nationalbank.kz/rss/rates_all.xml',
        'birg_key' => 'nationalkz',
    );
    $links['instaforex'] = array(
        'title' => 'Instaforex.com',
        'url' => 'https://quotes.instaforex.com/api/quotesTick?m=json&q=eurrur,usdrur',
        'birg_key' => 'instaforex',
    );
    $links['bitfinex'] = array(
        'title' => 'Bitfinex.com',
        'url' => 'https://api.bitfinex.com/v1/tickers?symbols',
        'birg_key' => 'bitfinex',
    );
    $links['binance'] = array(
        'title' => 'Binance.com (price)',
        'url' => 'https://api.binance.com/api/v3/ticker/price',
        'birg_key' => 'binance',
    );
    $links['binanceticker'] = array(
        'title' => 'Binance.com (bookTicker)',
        'url' => 'https://api.binance.com/api/v3/ticker/bookTicker',
        'birg_key' => 'binanceticker',
    );
    $links['blockchain'] = array(
        'title' => 'Blockchain.com',
        'url' => 'https://www.blockchain.com/ticker',
        'birg_key' => 'blockchain',
    );
    $links['exmo'] = array(
        'title' => 'Exmo.me',
        'url' => 'https://api.exmo.me/v1/ticker/',
        'birg_key' => 'exmo',
    );
    $links['poloniex'] = array(
        'title' => 'Poloniex.com',
        'url' => 'https://api.poloniex.com/markets/price',
        'birg_key' => 'poloniex',
    );
    $links['btc_alpha'] = array(
        'title' => 'Btc-alpha.com',
        'url' => 'https://btc-alpha.com/api/v1/ticker/?format=json',
        'birg_key' => 'btc_alpha',
    );
    $links['whitebit'] = array(
        'title' => 'Whitebit.com',
        'url' => 'https://whitebit.com/api/v4/public/ticker',
        'birg_key' => 'whitebit',
    );
    $links['bybit'] = array(
        'title' => 'Bybit.com',
        'url' => 'https://api.bybit.com/v5/market/tickers?category=spot',
        'birg_key' => 'bybit',
    );

    $links['coingeckothb'] = array(
        'title' => 'Coingecko.com (THB)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=THB&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckothb',
    );
    $links['coingeckoinr'] = array(
        'title' => 'Coingecko.com (INR)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=INR&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckoinr',
    );
    $links['coingeckotry'] = array(
        'title' => 'Coingecko.com (TRY)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=TRY&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckotry',
    );
    $links['coingeckojpy'] = array(
        'title' => 'Coingecko.com (JPY)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=JPY&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckojpy',
    );
    $links['coingeckocny'] = array(
        'title' => 'Coingecko.com (CNY)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=CNY&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckocny',
    );
    $links['coingeckohkd'] = array(
        'title' => 'Coingecko.com (HKD)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=HKD&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckohkd',
    );
    $links['coingeckoars'] = array(
        'title' => 'Coingecko.com (ARS)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=ARS&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckoars',
    );
    $links['coingeckoaed'] = array(
        'title' => 'Coingecko.com (AED)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=AED&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckoaed',
    );
    $links['coingeckopkr'] = array(
        'title' => 'Coingecko.com (PKR)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=PKR&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckopkr',
    );
    $links['coingeckongn'] = array(
        'title' => 'Coingecko.com (NGN)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=NGN&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckongn',
    );
    $links['coingeckogbp'] = array(
        'title' => 'Coingecko.com (GBP)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=GBP&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckogbp',
    );
    $links['coingeckoaud'] = array(
        'title' => 'Coingecko.com (AUD)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=AUD&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckoaud',
    );
    $links['coingeckocad'] = array(
        'title' => 'Coingecko.com (CAD)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=CAD&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckocad',
    );
    $links['coingeckochf'] = array(
        'title' => 'Coingecko.com (CHF)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=CHF&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckochf',
    );
    $links['coingeckosek'] = array(
        'title' => 'Coingecko.com (SEK)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=SEK&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckosek',
    );
    $links['coingeckomxn'] = array(
        'title' => 'Coingecko.com (MXN)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=MXN&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckomxn',
    );
    $links['coingeckosgd'] = array(
        'title' => 'Coingecko.com (SGD)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=SGD&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckosgd',
    );
    $links['coingeckopln'] = array(
        'title' => 'Coingecko.com (PLN)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=PLN&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckopln',
    );
    $links['coingeckobrl'] = array(
        'title' => 'Coingecko.com (BRL)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=BRL&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckobrl',
    );
    $links['coingeckodkk'] = array(
        'title' => 'Coingecko.com (DKK)',
        'url' => 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=DKK&ids=usd,tether,bitcoin,ethereum,usd-coin,ripple,cardano,dogecoin,tron,solana,polygon,polkadot,litecoin,binance-usd,dai,shiba-inu,avalanche,uniswap,chainlink,monero,ethereum-classic,stellar,toncoin,bitcoin-cash,aptos,arbitrum,vechain,near,pax-dollar,algorand,eos,optimism,tezos,decentraland,neo,bitcoin-sv,trust-wallet-token,binance-coin',
        'birg_key' => 'coingeckodkk',
    );
    $links['rapira'] = array(
        'title' => 'Rapira.net',
        'url' => 'https://api.rapira.net/open/market/rates',
        'birg_key' => 'rapira',
    );
    $links['cryptomus'] = array(
        'title' => 'Cryptomus.com',
        'url' => 'https://api.cryptomus.com/v2/exchange-rate/list',
        'birg_key' => 'cryptomus',
    );
    $links['heleket'] = array(
        'title' => 'Heleket.com',
        'url' => 'https://api.heleket.com/v2/exchange-rate/list',
        'birg_key' => 'heleket',
    );
    $links['huobi'] = array(
        'title' => 'Htx.com',
        'url' => 'https://api.htx.com/market/tickers',
        'birg_key' => 'huobi',
    );
    $links['okx'] = array(
        'title' => 'Okx.com',
        'url' => 'https://www.okx.com/api/v5/market/tickers?instType=SPOT',
        'birg_key' => 'okx',
    );
    $links['yobit'] = array(
        'title' => 'Yobit.net',
        'url' => 'https://yobit.net/tapipe/info/',
        'birg_key' => 'yobit',
    );
    $links['paylink'] = array(
        'title' => 'Paylink',
        'url' => 'https://paylink.finance/api/F3Tt5kfE45U8kCk',
        'birg_key' => 'paylink',
    );
    $links['merchant001'] = array(
        'title' => 'Merchant001.io',
        'url' => 'https://api.merchant001.io/v1/rate/source/all',
        'birg_key' => 'merchant001',
    );
    $links['kucoin'] = array(
        'title' => 'Kucoin.com',
        'url' => 'https://api.kucoin.com/api/v1/market/allTickers',
        'birg_key' => 'kucoin',
    );
    $links['koshelek'] = array(
        'title' => 'Koshelek.ru',
        'url' => 'https://api.koshelek.ru/api/v1/rates/all',
        'birg_key' => 'koshelek',
    );
    $links['payeer'] = array(
        'title' => 'Payeer.com',
        'url' => 'https://payeer.com/api/trade/ticker',
        'birg_key' => 'payeer',
    );
    $links['supermoney'] = array(
        'title' => 'SuperMoney',
        'url' => 'https://api-v2.moneyhoney.io/v2/merchant/currency-rates',
        'birg_key' => 'supermoney',
    );
    $links['bitbanker'] = array(
        'title' => 'Bitbanker.org',
        'url' => 'https://api.aws.bitbanker.org/latest/public/bestchange/rates',
        'birg_key' => 'bitbanker',
    );
    $links['utopia'] = array(
        'title' => 'Utopia',
        'url' => 'https://crp.is:8182/market/pairs',
        'birg_key' => 'utopia',
    );
    $links['goldex'] = array(
        'title' => 'Goldex',
        'url' => 'https://stage.goldex.space/json/exchange_rate',
        'birg_key' => 'goldex',
    );
    $links['mexc'] = [
        'title' => 'Mexc.com',
        'url' => 'https://api.mexc.com/api/v3/ticker/price',
        'birg_key' => 'mexc',
    ];
    $links['bithumb'] = [
        'title' => 'Bithumb.com (USDT/KRW)',
        'url' => 'https://api.bithumb.com/public/ticker/USDT_KRW',
        'birg_key' => 'bithumb',
    ];

    $tokenspot = [
        'btc_kgs' => ['BTC/KGS', 'btckgs'],
        'btc_rub' => ['BTC/RUB', 'btcrub'],
        'btcusd' => ['BTC/USD', 'btcusd'],
        'btc_usdc' => ['BTC/USDC', 'btcusdc'],
        'btc_usdt' => ['BTC/USDT', 'btcusdt'],
        'eth_btc' => ['ETH/BTC', 'ethbtc'],
        'eth_kgs' => ['ETH/KGS', 'ethkgs'],
        'eth_rub' => ['ETH/RUB', 'ethrub'],
        'ethusd' => ['ETH/USD', 'ethusd'],
        'eth_usdc' => ['ETH/USDC', 'ethusdc'],
        'eth_usdt' => ['ETH/USDT', 'ethusdt'],
        'trx_kgs' => ['TRX/KGS', 'trxkgs'],
        'trx_rub' => ['TRX/RUB', 'trxrub'],
        'trxusd' => ['TRX/USD', 'trxusd'],
        'trx_usdt' => ['TRX/USDT', 'trxusdt'],
        'usdc_kgs' => ['USDC/KGS', 'usdckgs'],
        'usdc_rub' => ['USDC/RUB', 'usdcrub'],
        'usdcusd' => ['USDC/USD', 'usdcusd'],
        'usdc_usdt' => ['USDC/USDT', 'usdcusdt'],
        'usdt_kgs' => ['USDT/KGS', 'usdtkgs'],
        'usdt_rub' => ['USDT/RUB', 'usdtrub'],
        'usdtusd' => ['USDT/USD', 'usdtusd'],
    ];

    foreach ($tokenspot as $k => $val) {
        $links["tokenspot_{$k}"] = [
            'title' => "Tokenspot.com ({$val[0]})",
            'url' => "https://api.tokenspot.com/api/v1/spot/depth?symbol={$val[1]}",
            'birg_key' => "tokenspot_{$k}",
        ];
    }

    $links['cryptocash'] = [
        'title' => 'Crypto-cash.world',
        'url' => 'https://crypto-cash.world/market/rates/export/json',
        'birg_key' => 'cryptocash',
    ];

    $links['abcex'] = [
        'title' => 'Abcex.io',
        'url' => 'https://gateway.abcex.io/api/v2/exchange/public/instrument/rates',
        'birg_key' => 'abcex',
    ];

    $links['grinex'] = [
        'title' => 'Grinex.io',
        'url' => 'https://grinex.io/rates',
        'birg_key' => 'grinex',
    ];

    $links['quickex'] = [
        'title' => 'Quickex.io',
        'url' => 'https://quickex.io/api/v2/rates/public/xml-v2-custom?path=/premium',
        'birg_key' => 'quickex',
    ];

    $links['nicepay'] = [
        'title' => 'Nicepay.io',
        'url' => 'https://nicepay.io/d/public/api/currentTradingRatesFromTo',
        'birg_key' => 'nicepay',
    ];

    $links['hybridrate'] = [
        'title' => 'Hybrid-rate.com',
        'url' => 'https://hybrid-rate.com/currency-rates.json',
        'birg_key' => 'hybridrate',
    ];

    return $links;
}

add_filter('new_parser_links', 'def_new_parser_links_sort', 10000);
function def_new_parser_links_sort($links) {

    uasort($links, fn($a, $b) => strcasecmp($a['title'], $b['title']));

    return $links;
}

add_filter('set_parser_pairs', 'def_set_parser_pairs', 10, 4);
function def_set_parser_pairs($parser_pairs, $output, $birg_key, $up_time) {

    $cs = 20;

    if ('hybridrate' == $birg_key) {

        $r = pn_json_decode($output);

        if (!empty($r)) {
            foreach ($r as $key => $item) {
                $exp = explode('-', $key);

                $give = mb_strtolower(is_isset($exp, 0));
                $get = mb_strtolower(is_isset($exp, 1));
                $type = mb_strtolower(is_isset($exp, 2));
                $rate = is_sum($item, $cs);
                if (!$give || !$rate) continue;

                $k = implode('_', array_filter([$birg_key, $give, $get, $type]));
                $parser_pairs[$k] = [
                    'title' => $type,
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => mb_strtoupper($give),
                    'get' => mb_strtoupper($get),
                    'up' => $up_time,
                ];
            }
        }

    }

    if ('nicepay' == $birg_key) {

        $r = pn_json_decode($output);

        if (!empty($r['data']['rates'])) {
            foreach ($r['data']['rates'] as $val) {
                $in = is_sum($val['in'] / 100, $cs);
                $out = is_sum($val['out'] / 100, $cs);
                if (!$in || !$out) continue;

                $rate = is_sum($out / $in, $cs);
                if (!$rate) continue;

                $type = '';
                $give = mb_strtolower($val['from']);
                $get = mb_strtolower($val['to']);

                $k = "{$birg_key}_{$give}{$get}";
                if ($type) $k .= "_{$type}";

                $parser_pairs[$k] = [
                    'title' => $type,
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => mb_strtoupper($give),
                    'get' => mb_strtoupper($get),
                    'up' => $up_time,
                ];
            }
        }

    }

    if ('grinex' == $birg_key) {

        $r = pn_json_decode($output);

        if (!empty($r)) {
            $types = ['buy', 'sell'];
            foreach ($r as $key => $item) {
                foreach ($types as $type) {
                    $rate = is_sum(is_isset($item, $type), $cs);
                    if (!$rate) continue;

                    $give = mb_strtolower($key);
                    $get = '';

                    $parser_pairs["{$birg_key}_{$give}{$get}_{$type}"] = [
                        'title' => $type,
                        'course' => $rate,
                        'birg' => $birg_key,
                        'give' => mb_strtoupper($give),
                        'get' => mb_strtoupper($get),
                        'up' => $up_time,
                    ];
                }
            }
        }

    }

    if ('cryptocash' == $birg_key) {

        $r = pn_json_decode($output);

        if (is_array($r) && isset($r['rates'])) {
            foreach ($r['rates'] as $item) {
                if (!isset($item['give'], $item['get'])) continue;

                $types = ['lastPrice', 'buy', 'sell'];
                foreach ($types as $type) {
                    $rate = is_sum(is_isset($item, $type), $cs);
                    if (!$rate) continue;

                    $give = strtolower($item['give']);
                    $get = strtolower($item['get']);

                    $parser_pairs["{$birg_key}_{$give}{$get}_{$type}"] = [
                        'title' => $type,
                        'course' => $rate,
                        'birg' => $birg_key,
                        'give' => strtoupper($give),
                        'get' => strtoupper($get),
                        'up' => $up_time,
                    ];
                }
            }
        }

    }

    if (strstr($birg_key, 'tokenspot_')) {
        $res = @json_decode($output, true);
        if (isset($res['asks'], $res['bids']) and is_array($res['asks']) and is_array($res['bids'])) {
            foreach ($res['asks'] as $out) {
                $pair = str_replace('tokenspot_', '', $birg_key);
                $title_arr = explode('_', $pair);
                $rate = (string)is_isset($out, 'price');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_asks';
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'title' => 'Asks',
                        'give' => strtoupper(is_isset($title_arr, 0)),
                        'get' => strtoupper(is_isset($title_arr, 1)),
                        'up' => $up_time,
                    );
                }
                break;
            }

            foreach ($res['bids'] as $out) {
                $pair = str_replace('tokenspot_', '', $birg_key);
                $title_arr = explode('_', $pair);
                $rate = (string)is_isset($out, 'price');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_bids';
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'title' => 'Bids',
                        'give' => strtoupper(is_isset($title_arr, 0)),
                        'get' => strtoupper(is_isset($title_arr, 1)),
                        'up' => $up_time,
                    );
                }
                break;
            }
        }
    }

    if ('bithumb' == $birg_key) {

        $r = pn_json_decode($output);

        if (is_array($r) && isset($r['data'])) {
            $types = array('closing_price');
            foreach ($types as $type) {
                $rate = is_sum(is_isset($r['data'], $type), $cs);
                if (!$rate) continue;

                $give = strtolower('USDT');
                $get = strtolower('KRW');

                $parser_pairs["{$birg_key}_{$give}{$get}_{$type}"] = [
                    'title' => $type,
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => strtoupper($give),
                    'get' => strtoupper($get),
                    'up' => $up_time,
                ];
            }
        }

    }

    if ('mexc' == $birg_key) {

        $r = pn_json_decode($output);

        if (is_array($r) && count($r)) {
            foreach ($r as $item) {
                if (!isset($item['symbol'], $item['price'])) continue;

                $rate = is_sum($item['price'], $cs);
                if (!$rate) continue;

                $give = strtoupper($item['symbol']);
                $get = '';

                $parser_pairs[$birg_key . '_' . strtolower($give) . strtolower($get)] = [
                    'title' => '',
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => $give,
                    'get' => $get,
                    'up' => $up_time,
                ];

            }
        }

    }

    if ('goldex' == $birg_key) {

        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['data'])) {

            foreach ($res['data'] as $pd) {
                if (isset($pd['value'], $pd['currency'], $pd['currency']['name'])) {

                    $rate = $pd['value'];
                    $rate = is_sum($rate, $cs);
                    if ($rate) {

                        $parser_pair_key = $birg_key . '_usdt' . strtolower($pd['currency']['id']);
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => '',
                            'give' => 'USDT',
                            'get' => strtoupper($pd['currency']['name']),
                            'up' => $up_time,
                        );

                    }

                }
            }

        }
    }

    if ('utopia' == $birg_key) {

        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['result'], $res['result']['pairs'])) {

            foreach ($res['result']['pairs'] as $pd) {
                if (isset($pd['pair'], $pd['pair']['pair'], $pd['data_market'])) {

                    $pair_arr = explode('_', strtoupper($pd['pair']['pair']));

                    $arrs = array('open', 'close', 'high', 'low');
                    foreach ($arrs as $arr) {

                        $rate = is_isset($pd['data_market'], $arr);
                        $rate = is_sum($rate, $cs);
                        if ($rate) {

                            $parser_pair_key = $birg_key . '_' . strtolower(is_isset($pair_arr, 0)) . strtolower(is_isset($pair_arr, 1)) . '_' . $arr;
                            $parser_pairs[$parser_pair_key] = array(
                                'course' => $rate,
                                'birg' => $birg_key,
                                'title' => $arr,
                                'give' => is_isset($pair_arr, 0),
                                'get' => is_isset($pair_arr, 1),
                                'up' => $up_time,
                            );

                        }

                    }
                }
            }

        }
    }

    if ('supermoney' == $birg_key) {

        $res = @json_decode($output, true);
        if (is_array($res)) {
            $r = 0;
            foreach ($res as $pd) {
                if (isset($pd['sourceRate'])) {
                    $r++;
                    $rate = is_isset($pd, 'sourceRate');
                    $rate = is_sum($rate, $cs);
                    if ($rate) {

                        $give = pn_strip_input(is_isset($pd, 'outcomeCurrency'));
                        $get = pn_strip_input(is_isset($pd, 'incomeCurrency'));
                        $pair_key = $give . $get;

                        $parser_pair_key = $birg_key . '_' . strtolower($pair_key) . '_' . strtolower(pn_strip_input(is_isset($pd, 'method'))) . '_' . strtolower(pn_strip_input(is_isset($pd, 'direction')));
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => pn_strip_input(is_isset($pd, 'method')) . '&nbsp;(' . pn_strip_input(is_isset($pd, 'direction')) . ')',
                            'give' => $give,
                            'get' => $get,
                            'up' => $up_time,
                        );

                    }

                    if (2 == $r) {
                        //break;
                    }
                }
            }
        }
    }

    if ('payeer' == $birg_key) {

        $res = @json_decode($output, true);
        if (isset($res['pairs']) and is_array($res['pairs'])) {
            foreach ($res['pairs'] as $pair => $pd) {
                $title_arr = explode('_', $pair);
                $arrs = array('ask', 'bid', 'last');
                foreach ($arrs as $key) {
                    $rate = is_isset($pd, $key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {

                        $give = is_isset($title_arr, 0);
                        $get = is_isset($title_arr, 1);
                        $pair_key = $give . $get;

                        $parser_pair_key = $birg_key . '_' . strtolower($pair_key) . '_' . $key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $key,
                            'give' => $give,
                            'get' => $get,
                            'up' => $up_time,
                        );

                    }
                }
            }
        }
    }

    if ('koshelek' == $birg_key) {
        $res = @json_decode($output, true);
        if (isset($res['rates']) and is_array($res['rates'])) {
            foreach ($res['rates'] as $symbol => $rate) {
                $title_arr = explode('|', $symbol);
                $give = trim(is_isset($title_arr, 0));
                $get = trim(is_isset($title_arr, 1));
                $rate = is_sum($rate, $cs);
                if ($rate > 0) {
                    $parser_pair_key = $birg_key . '_' . strtolower($give . '_' . $get);
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'give' => strtoupper($give),
                        'get' => strtoupper($get),
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('kucoin' == $birg_key) {
        $res = @json_decode($output, true);
        if (isset($res['data']['ticker']) and is_array($res['data']['ticker'])) {
            foreach ($res['data']['ticker'] as $d) {
                $title_arr = explode('-', is_isset($d, 'symbol'));
                $give = trim(is_isset($title_arr, 0));
                $get = trim(is_isset($title_arr, 1));
                $arrs = array('buy', 'sell', 'high', 'low', 'last');
                foreach ($arrs as $arr) {
                    $rate = is_sum(is_isset($d, $arr), $cs);
                    if ($rate > 0) {
                        $parser_pair_key = $birg_key . '_' . strtolower($give . '_' . $get) . '_' . $arr;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $arr,
                            'give' => strtoupper($give),
                            'get' => strtoupper($get),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if (strstr($birg_key, 'coingecko')) {
        $res = @json_decode($output, true);
        if (is_array($res)) {
            $get = str_replace('coingecko', '', $birg_key);
            foreach ($res as $d) {
                if (isset($d['symbol'])) {
                    $give = trim(is_isset($d, 'symbol'));
                    $give = str_replace(array('+', '-', '_'), '', $give);
                    $rate = is_sum(is_isset($d, 'current_price'), $cs);
                    if ($rate > 0) {
                        $parser_pair_key = $birg_key . '_' . strtolower($give . '_' . $get);
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'give' => strtoupper($give),
                            'get' => strtoupper($get),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('merchant001' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res)) {
            foreach ($res as $out) {
                $rate = $out['sourceRate'];
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $give = $out['outcomeCurrency'];
                    $get = $out['incomeCurrency'];
                    $the_title = strtolower($out['direction']) . '_' . strtolower($out['method']);
                    $parser_pair_key = $birg_key . '_' . strtolower($give) . strtolower($get) . '_' . $the_title;
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'title' => $out['direction'] . ' ' . $out['method'],
                        'give' => $give,
                        'get' => $get,
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('paylink' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['data'], $res['data']['rate'])) {
            $rate = $res['data']['rate'];
            $rate = is_sum($rate, $cs);
            if ($rate) {
                $parser_pair_key = $birg_key . '_usdtuah';
                $parser_pairs[$parser_pair_key] = array(
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => 'USDT',
                    'get' => 'UAH',
                    'up' => $up_time,
                );
            }
        }
    }

    if ('binance' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $out) {
                $title_in = is_isset($out, 'symbol');
                $rate = (string)is_isset($out, 'price');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_' . mb_strtolower($title_in);
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'give' => mb_substr($title_in, 0, 3),
                        'get' => mb_substr($title_in, 3, 7),
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('moex' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['marketdata'])) {
            $rate = is_sum($res['marketdata']['data'][0][8], $cs);
            if ($rate > 0) {
                $parser_pair_key = $birg_key . '_usdrub';
                $parser_pairs[$parser_pair_key] = array(
                    'course' => $rate,
                    'birg' => $birg_key,
                    'give' => 'USD',
                    'get' => 'RUB',
                    'up' => $up_time,
                );
            }
        }
    }

    if ('instaforex' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $out) {
                $title_in = is_isset($out, 'symbol');
                $arrs = array('ask', 'bid');
                foreach ($arrs as $arr_value) {
                    $rate = (string)is_isset($out, $arr_value);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($title_in) . '_' . mb_strtolower($arr_value);
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $arr_value,
                            'give' => mb_substr($title_in, 0, 3),
                            'get' => mb_substr($title_in, 3, 7),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if (in_array($birg_key, ['heleket', 'cryptomus'])) {
        $res = @json_decode($output, true);
        if (isset($res['result']) and is_array($res['result'])) {
            foreach ($res['result'] as $give => $vals) {
                if (is_array($vals)) {
                    foreach ($vals as $get => $amount) {
                        $rate = is_sum($amount, $cs);
                        if ($rate) {
                            $parser_pair_key = $birg_key . '_' . mb_strtolower($give) . '_' . mb_strtolower($get);
                            $parser_pairs[$parser_pair_key] = array(
                                'course' => $rate,
                                'birg' => $birg_key,
                                'give' => $give,
                                'get' => $get,
                                'up' => $up_time,
                            );
                        }
                    }
                }
            }
        }
    }

    if ('whitebit' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res)) {
            foreach ($res as $title_in => $out) {
                $title_arr = explode('_', $title_in);
                $rate = (string)is_isset($out, 'last_price');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_' . mb_strtolower(is_isset($title_arr, 0)) . '_' . mb_strtolower(is_isset($title_arr, 1));
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'give' => is_isset($title_arr, 0),
                        'get' => is_isset($title_arr, 1),
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('bybit' == $birg_key) {
        $res = @json_decode($output, true);
        if (isset($res['result'], $res['result']['list']) and is_array($res['result']['list'])) {
            foreach ($res['result']['list'] as $out) {
                $title_in = is_isset($out, 'symbol');
                $c_title_in = mb_strlen($title_in);
                $rate = (string)is_isset($out, 'lastPrice');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_' . mb_strtolower($title_in) . '_lastprice';
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'title' => 'lastPrice',
                        'give' => mb_substr($title_in, 0, ($c_title_in - 4)),
                        'get' => mb_substr($title_in, ($c_title_in - 4), $c_title_in),
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('binanceticker' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $out) {
                $title_in = is_isset($out, 'symbol');
                $arrs = array('bidPrice', 'askPrice');
                foreach ($arrs as $arr_value) {
                    $rate = (string)is_isset($out, $arr_value);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($title_in) . '_' . mb_strtolower($arr_value);
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $arr_value,
                            'give' => mb_substr($title_in, 0, 3),
                            'get' => mb_substr($title_in, 3, 7),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('blockchain' == $birg_key) {
        $res = @json_decode($output);
        if (is_object($res)) {
            foreach ($res as $title => $out) {
                $arrs = array('15m', 'last', 'buy', 'sell');
                foreach ($arrs as $arr_value) {
                    $rate = (string)is_isset($out, $arr_value);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_btc' . mb_strtolower($title) . '_' . $arr_value;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $arr_value,
                            'give' => 'BTC',
                            'get' => mb_strtoupper($title),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('bitfinex' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $out) {
                $title_in = is_isset($out, 'pair');
                $narr = array('mid', 'bid', 'ask', 'last_price', 'low', 'high');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($out, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($title_in) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => mb_substr($title_in, 0, 3),
                            'get' => mb_substr($title_in, 3, 7),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('huobi' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['data']) and is_array($res['data'])) {
            foreach ($res['data'] as $d) {

                $title = mb_strtoupper(is_isset($d, 'symbol'));
                $narr = array('open', 'high', 'low', 'close', 'bid', 'ask');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($d, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($title) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => mb_substr($title, 0, 3),
                            'get' => mb_substr($title, 3, 7),
                            'up' => $up_time,
                        );
                    }
                }

            }
        }
    }

    if ('okx' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['data']) and is_array($res['data'])) {
            foreach ($res['data'] as $d) {
                $title = mb_strtoupper(is_isset($d, 'instId'));
                $title_arr = explode('-', $title);
                $narr = array('last', 'askPx', 'bidPx');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($d, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower(str_replace('-', '', $title)) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => is_isset($title_arr, 0),
                            'get' => is_isset($title_arr, 1),
                            'up' => $up_time,
                        );
                    }
                }

            }
        }
    }

    if ('yobit' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res) and isset($res['spot']) and is_array($res['spot'])) {
            foreach ($res['spot'] as $d) {
                $narr = array('last', 'buy', 'sell');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($d, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($d['ticker1'] . $d['ticker2']) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => $d['ticker1'],
                            'get' => $d['ticker2'],
                            'up' => $up_time,
                        );
                    }
                }

            }
        }
    }

    if ('poloniex' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $v) {
                $title_in = is_isset($v, 'symbol');
                $title_arr = explode('_', $title_in);
                $rate = (string)is_isset($v, 'price');
                $rate = is_sum($rate, $cs);
                if ($rate) {
                    $parser_pair_key = $birg_key . '_' . mb_strtolower(str_replace('_', '', $title_in));
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate,
                        'birg' => $birg_key,
                        'title' => '',
                        'give' => mb_strtoupper(is_isset($title_arr, 0)),
                        'get' => mb_strtoupper(is_isset($title_arr, 1)),
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('btc_alpha' == $birg_key) {
        $res = @json_decode($output);
        if (is_array($res)) {
            foreach ($res as $v) {
                $title_in = $v->pair;
                $title_arr = explode('_', $title_in);

                $narr = array('last', 'diff', 'vol', 'high', 'low', 'buy', 'sell');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($v, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower(str_replace('_', '', $title_in)) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => is_isset($title_arr, 0),
                            'get' => is_isset($title_arr, 1),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('exmo' == $birg_key) {
        $res = @json_decode($output);
        if (is_object($res)) {
            foreach ($res as $title_in => $v) {
                $title_arr = explode('_', $title_in);
                $narr = array('buy_price', 'sell_price', 'last_trade', 'high', 'low', 'avg');
                foreach ($narr as $res_key) {
                    $rate = (string)is_isset($v, $res_key);
                    $rate = is_sum($rate, $cs);
                    if ($rate) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower(str_replace('_', '', $title_in)) . '_' . $res_key;
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $res_key,
                            'give' => is_isset($title_arr, 0),
                            'get' => is_isset($title_arr, 1),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('bankgovua' == $birg_key) {
        if (strstr($output, '<?xml')) {
            $res = @simplexml_load_string($output);
            if (is_object($res) and isset($res->currency)) {
                foreach ($res->currency as $data) {
                    $CharCode = (string)$data->cc;
                    $CharCode = trim($CharCode); // type

                    $course = (string)$data->rate;
                    $course = is_sum($course, $cs);

                    if ($course > 0) {
                        $parser_pair_key = $birg_key . '_' . strtolower($CharCode) . 'uah';
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $course,
                            'birg' => $birg_key,
                            'give' => $CharCode,
                            'get' => 'UAH',
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('nationalkz' == $birg_key) {
        if (strstr($output, '<?xml')) {
            $res = @simplexml_load_string($output);
            if (is_object($res) and isset($res->channel)) {
                foreach ($res->channel->item as $data) {

                    $CharCode = $data->title;
                    $CharCode = trim($CharCode); // type

                    $nominal = (string)$data->quant; // 1 USD
                    $nominal = is_sum($nominal, $cs);

                    $value = (string)$data->description; // ? KZT
                    $value = is_sum($value, $cs);

                    if ($nominal > 0 and $value > 0) {
                        $course = is_sum($value / $nominal, $cs);
                        $parser_pair_key = $birg_key . '_' . strtolower($CharCode) . 'kzt';
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $course,
                            'birg' => $birg_key,
                            'give' => $CharCode,
                            'get' => 'KZT',
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('cbr' == $birg_key) {
        if (strstr($output, '<?xml')) {
            $res = @simplexml_load_string($output);
            if (is_object($res)) {
                if (isset($res->Valute)) {
                    $currencies = $res->Valute;
                    foreach ($currencies as $c_obj) {
                        $CharCode = (string)$c_obj->CharCode;
                        $CharCode = trim($CharCode); // type

                        $nominal = (string)$c_obj->Nominal;
                        $nominal = is_sum($nominal, $cs);

                        $value = (string)$c_obj->Value;
                        $value = is_sum($value, $cs);

                        if ($nominal > 0 and $value > 0) {
                            $course = is_sum($value / $nominal, $cs);
                            $parser_pair_key = $birg_key . '_' . strtolower($CharCode) . 'rub';
                            $parser_pairs[$parser_pair_key] = array(
                                'course' => $course,
                                'birg' => $birg_key,
                                'give' => $CharCode,
                                'get' => 'RUB',
                                'up' => $up_time,
                            );
                        }
                    }
                }
            }
        }
    }

    if ('ecb' == $birg_key) {
        if (strstr($output, '<?xml')) {
            $res = @simplexml_load_string($output);
            if (is_object($res) and isset($res->Cube, $res->Cube->Cube)) {
                foreach ($res->Cube->Cube->Cube as $cube) {
                    $currency = (string)$cube['currency'];
                    $currency = trim($currency);

                    $rate = (string)$cube['rate'];
                    $rate = is_sum($rate, $cs);

                    if ($rate > 0) {
                        $parser_pair_key = $birg_key . '_eur' . strtolower($currency);
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'give' => 'EUR',
                            'get' => $currency,
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('nbu' == $birg_key) {
        if (strstr($output, '<?xml')) {
            $res = @simplexml_load_string($output);
            if (is_object($res) and isset($res->currency)) {
                foreach ($res->currency as $val) {
                    $currency = (string)$val->cc;
                    $currency = trim($currency);

                    $rate = (string)$val->rate;
                    $rate = is_sum($rate, $cs);

                    if ($rate > 0) {
                        $parser_pair_key = $birg_key . '_' . strtolower($currency) . 'uah';
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'give' => $currency,
                            'get' => 'UAH',
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    if ('privat' == $birg_key or 'privat24' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res)) {
            foreach ($res as $val) {
                $currency = trim(is_isset($val, 'ccy'));

                $rate1 = trim(is_isset($val, 'buy'));
                $rate1 = is_sum($rate1, $cs);

                if ($rate1 > 0) {
                    $parser_pair_key = $birg_key . '_' . strtolower($currency) . '_uah_buy';
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate1,
                        'birg' => $birg_key,
                        'title' => 'buy',
                        'give' => $currency,
                        'get' => 'UAH',
                        'up' => $up_time,
                    );
                }

                $rate2 = trim(is_isset($val, 'sale'));
                $rate2 = is_sum($rate2, $cs);

                if ($rate2 > 0) {
                    $parser_pair_key = $birg_key . '_' . strtolower($currency) . '_uah_sale';
                    $parser_pairs[$parser_pair_key] = array(
                        'course' => $rate2,
                        'birg' => $birg_key,
                        'title' => 'sale',
                        'give' => $currency,
                        'get' => 'UAH',
                        'up' => $up_time,
                    );
                }
            }
        }
    }

    if ('rapira' == $birg_key) {
        $res = @json_decode($output, true);
        if (is_array($res)) {
            if (isset($res['data']) and is_array($res['data'])) {
                foreach ($res['data'] as $value) {
                    $give = is_isset($value, 'quoteCurrency');
                    $get = is_isset($value, 'baseCurrency');
                    $narr = array('close', 'askPrice', 'bidPrice');
                    foreach ($narr as $res_key) {
                        $rate = is_isset($value, $res_key);
                        $rate = is_sum($rate, $cs);
                        if ($rate) {
                            $parser_pair_key = strtolower($birg_key . '_' . $give . $get . '_' . $res_key);
                            $parser_pairs[$parser_pair_key] = array(
                                'course' => $rate,
                                'birg' => $birg_key,
                                'title' => $res_key,
                                'give' => $give,
                                'get' => $get,
                                'up' => $up_time,
                            );
                        }
                    }
                }
            }
        }
    }

    $xml_arr = ['bitbanker', 'abcex', 'quickex'];
    if (strstr($birg_key, 'xmlc_') or in_array($birg_key, $xml_arr)) {
        //$now_birg_key = str_replace('xmlc_', '', $birg_key);
        if (strstr($output, '<?xml') || 'quickex' == $birg_key) {
            $res = @simplexml_load_string($output);
            if (is_object($res)) {
                foreach ($res->item as $res_key) {
                    $from = (string)is_isset($res_key, 'from');
                    $to = (string)is_isset($res_key, 'to');
                    $in = (string)is_isset($res_key, 'in');
                    $in = is_sum($in, $cs);
                    $out = (string)is_isset($res_key, 'out');
                    $out = is_sum($out, $cs);
                    $city = (string)is_isset($res_key, 'city');
                    $city = trim($city);

                    $rate = 0;
                    if ($in > 0 and $out > 0) {
                        $rate = $out / $in;
                    }
                    $rate = is_sum($rate, $cs);
                    if ($rate > 0) {
                        $parser_pair_key = $birg_key . '_' . mb_strtolower($from . $to) . '_' . $city;
                        $parser_pair_key = rtrim($parser_pair_key, '_');
                        $parser_pairs[$parser_pair_key] = array(
                            'course' => $rate,
                            'birg' => $birg_key,
                            'title' => $city,
                            'give' => mb_strtoupper($from),
                            'get' => mb_strtoupper($to),
                            'up' => $up_time,
                        );
                    }
                }
            }
        }
    }

    return $parser_pairs;
}

add_filter('work_parser_links', 'def_work_parser_links');
function def_work_parser_links($links) {

    $birgs = apply_filters('new_parser_links', array());

    $work_birgs = get_option('work_birgs');
    if (!is_array($work_birgs)) {
        $work_birgs = array();
    }

    foreach ($birgs as $birg_key => $birg_data) {
        if (in_array($birg_key, $work_birgs)) {
            $links[$birg_key] = $birg_data;
        }
    }

    return $links;
}