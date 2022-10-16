from django.shortcuts import render
from django.http import HttpResponse
import json
import pykakasi
import spacy
import time



nlp = spacy.load("ja_core_news_sm")
nlp.max_length = 1500000
hiraganaConverter = pykakasi.kakasi()

# Create your views here.
def tokenizer(request):
    start = time.time()
    nlp.max_length = 1500000
    if request.method == 'POST':
        postData = json.loads(request.body)
        jsonWords = list()
        print(len(postData['raw_text']))
        doc = nlp(postData['raw_text'], disable = ['ner'])
        for sentenceIndex, sentence in enumerate(doc.sents):
            for token in sentence:
                reading = list()
                lemmaReading = list()
                #get reading
                result = hiraganaConverter.convert(token.text)
                for x in result:
                    reading.append(x['hira'])
                
                #get lemma reading
                result = hiraganaConverter.convert(token.lemma_)
                for x in result:
                    lemmaReading.append(x['hira'])

                jsonWords.append({'w': str(token.text), 'r': ''.join(reading), 'l': token.lemma_, 'lr': ''.join(lemmaReading), 'pos': token.pos_,'si': sentenceIndex})
    #print(len(jsonWords))
    #print(time.time() - start)
    return HttpResponse(json.dumps(jsonWords), content_type="application/json")