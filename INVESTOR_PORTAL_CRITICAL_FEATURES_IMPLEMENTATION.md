ext00 hover:ttext-gray-6class="          "
        ment)(docueditDocument"click=        @      ton
           <but       on>
  butt  </             ad
     Downlo              >
         "
       smtext-ue-700 over:text-bl he-600ext-blu="t       class         d)"
  t(document.imenownloadDocu@click="d                 
    <button          
   p-2">s-center galex items="f<div clas              
             iv>
     </d        v>
       </di
           /div>           <     >
  oads</span} downl }nload_countcument.dow <span>{{ do                   /span>
ize }}<ed_file_sent.formattan>{{ docum        <sp           an>
 te) }}</spd_dauploate(document.>{{ formatDapan   <s       
          gray-400">text-t-xs p-4 mt-1 texs-center ga"flex item<div class=              /p>
    tion }}<ent.descripdocum>{{ 00"gray-5 text-t-smex"tclass=   <p                tle }}</p>
document.ti">{{ ay-900m text-gr font-mediuxt-smp class="te         <         ">
ss="ml-4   <div cla          >
     </div          
    />n="true"  aria-hiddeay-600"text-gr"h-6 w-6 s=entIcon clasDocum         <         
enter">er justify-ctems-centg flex iounded-lgray-100 r-10 h-10 bg- w-shrink-0="flex<div class           
     r">s-centeex itemflv class="  <di     >
       -between"er justify-centms ite flexy-4x-6 p class="pd"document.its" :key="ocumenata.dn categoryD imentfor="docu     <div v-      y-200">
 ra divide-g"divide-yv class=   <di     
          div>
   </       h3>
          </ }}
    elabtegoryData.l{{ ca            >
  " /n="truearia-hiddemr-2" -5 ass="h-5 wn)" clcoyData.i(categorentmponconCo="getIponent :is <com          nter">
    items-ce0 flexext-gray-90d tolfont-semibg "text-llass=  <h3 c        >
  y-200"ra border-g4 border-bpx-6 py-ss="    <div cla>
      hadow"lg snded-ite rou"bg-whass=gory" clate="c" :keymentspedDocurougory) in gata, cateoryDr="(categv v-fo        <dice-y-6">
s="spaclas  <div    ry -->
 egoy Cat bnts<!-- Docume     

    </div>n>
   /butto    <nt
    oad Docume        Upl>
  "true" /=ia-hiddenne" arinli5 mr-2 5 w-ss="h-on clalusIc    <P>
             
 colors"n-nsitioue-700 traover:bg-bl-lg hte rounded-whi-600 textbg-blue-4 py-2 class="px          ue"
 trodal =oadM"showUpllick=@c      utton
    
        <b </div>      p>
 rs</e to investos accessibldocument">Manage t-1gray-600 m="text-   <p class
       </h1>cumentsor Do900">Investtext-gray-nt-bold xl foext-2h1 class="t        <
  <div>
        etween">ustify-b j-center flex itemslass="mb-6 c<div-->
      -- Header   <!">
     class="p-6
    <divyout>
  <AdminLaate>templ
<ndex.vue -->s/Iumentestor/DocInvmin/js/pages/Ad- resources/```vue
<!-ent

ment ManagemDocu 1. Admin 
####n
tioentantend Implem# Fro

##
}
```  }
  );nsion(nalExteentOrigile->getCli $fid() . '.' .. uniqi) . '_' rn time(retu     {
      
 string: dFile $file)Name(UploadeiqueFilenerateUnn gete functio

    priva
    }        });
e too large' sizeption('Fileew \Exc     throw n       t
 limi 10MB { //* 1024)0 * 1024 > 1getSize() file->f ($

        i
        });wed'allot ype noile teption('FExchrow new \  t
          pes)) {wedMimeTy(), $allo>getMimeType$file-(!in_array( if 
               ];
mage/png'
         'i   peg',
  'image/j        t',
  heetml.sheedst.spreadocumencemats-offixmlfornd.opention/vicaappl '        
   s-excel',tion/vnd.m    'applica',
        ntngml.documessi.wordproceocumentts-officednxmlformaon/vnd.opelicati  'app  ,
        word'ation/ms 'applic           f',
/pdtion   'applica    = [
     imeTypes $allowedM       
    {
 file): voidloadedFile $(UplidateFile vationate func

    priv
    }];      e()
  eTypimgetMment->cu$do> pe' =    'mime_ty
        ),ileName(->getFdocumentame' => $          'n,
  lePath())ent->getFipath($documprivate')->(':disk => Storage:     'path'    eturn [
    r

       ;ntId)$document(DownloadCouincrement->ryitoposdocumentRe$this->     unt
   nload cocrement dow       // In    
 ent);
    ess, $userAg $ipAddrcumentId,tId, $dounvestorAccoccess($in>logApository-mentRecuis->do     $thcess
   Log ac/    /   }

          nd');
 not fountDocumeption(' new \Excethrow     
       document) {   if (!$
            entId);
 d($documfindByIitory->entReposis->docum $tht =umen $doc      
    {
 nt): arrayuserAge string $dress,ing $ipAdntId, strAccoustor int $inve$documentId,ent(int ocum downloadDfunction
    public     }

uped;turn $gro   re}

     t;
        ocumen][] = $d['documents'gory]teouped[$ca         $gr      }
   
           ];         ' => []
  cuments        'do           ory(),
 nt->getCateg => $documecategory'       '             [
 egory] =d[$catupe $gro              {
 ry])) go[$categroupedisset($    if (!
        )->value();ategory(t->getCocumen= $dory  $categ        ment) {
    $docuts asch ($documen  forea
      [];= ed   $groupory
      y categGroup b        //  

       entRoundId);estmor($invibleToInvestisy->findVentRepositor->docum $thisnts =    $docume  {
    ray
  oundId): arnvestmentRt $investor(inntsForIcumeetDounction gic f
    publ  }
  ment;
rn $docutu   re
     ocument);
y->save($depositordocumentR    $this->    

     );  TIVE
 us::ACumentStatstatus: Doc           Id,
 mentRoundd: $investtmentRoundI    inves
        ibleToAll,l: $visibleToAl     vis     edBy,
  : $uploadadedBy   uplo      e(),
   TimeImmutablateew \DploadDate: n           upe(),
 ->getMimeTyeType: $file  mim    
      getSize(),file->leSize: $        fime(),
    OriginalNantlieile->getCleName: $f  fi        lePath,
  h: $fiilePat         f
   ),goryteng($ca:fromStriry:egocumentCatry: Docatego          n,
  ptio: $descriescription   d,
         itle: $title         try
   ositoe set by repill b 0, // Wid:           cument(
 w InvestorDoument = ne   $docy
     nt entit docume/ Create      /');

  'privateame, ', $fileNcumentsr-donvesto->storeAs('i = $file   $filePath     tore file
/ S    /          
  ile);
ileName($fueFerateUniq$this->gename =   $fileNe
      namique fileenerate un      // G

  le);ile($fi->validateF    $thisfile
    te Valida/        /ment {
 cunvestorDo): I   
 = nullId tmentRound ?int $inves       e,
rueToAll = tisibl    bool $v
    dedBy,loa     int $up
   ory,$categ    string n,
    iptio $descring   str    e,
 $titl string      e $file,
  iloadedF Upl    nt(
   dDocumection uploaun flic   pub

 }
    ) {ositorymentRepe $docufacntersitoryIDocumentRepovestorprivate In        t(
strucon __conlic functi   pub
{
 eervicgementSentManass Documge;

claStoras\port\FacadeSupte\ IlluminaFile;
use\Uploadedpinate\Httse Illum
uentStatus;\DocumjectsalueOb\Vnvestormain\I\Dop Apgory;
usementCateects\DocuValueObjstor\Inven\\Domai;
use AppterfaceositoryInrDocumentRepnvestoes\Isitori\Reponvestor\Domain\It;
use AppDocumen\Investorntitiesestor\Env\Domain\Ie App;

uscesestor\Servimain\Invace App\Domespphp

naice.php
<?ServanagementtMmen/Docuvices/Serain/Investor/ app/Domhp
/```payer

 Lervice

#### 3. S```
}
nt): void;erAge string $us$ipAddress,ring cumentId, stint $do, Idccount $investorAintgAccess(ction lo fun publicd;
   ): voint $id(iCountmentDownloadncrenction ipublic fu    id;
): vo(int $iddeleteion  functblicpu
    d;oi): vocumentocument $dtorDveson save(Infunctiblic ay;
    puarrl(): ndAlunction fi   public f;
 Id): arrayentRound$investmr(int bleToInvesto findVisiionlic funct
    pub array;$category):ry tCategory(DocumenyCategonction findBlic fu
    puborDocument;Investd): ?dById(int $i finunction    public f
{
rfacenteoryIentReposittorDocumnvesinterface Iy;

Categorents\DocumctlueObjestor\Vaomain\Inve
use App\D;umentnvestorDoc\I\EntitiestoresDomain\Invse App\

uries;ositor\Rep\InvestoainDompace App\p

names<?phce.php
oryInterfaositmentRepDocues/Investorepositoritor/RDomain/Invespp// a
```php
/erface
ository Int## 2. Rep`

##}
}
`` };
    ,
       'Draft'=> RAFT ::Dlf      sed',
      'ArchiveVED => f::ARCHIsel             'Active',
E =>CTIV    self::A {
        atch($this)n m     retur
    {
   gel(): strintion labfunc   public 

 t';T = 'draf DRAF   caseed';
 D = 'archivHIVERCcase A';
    E = 'active  case ACTIV
  {tring
Status: sum Documentcts;

enObjealueor\V\Invest\Domainspace App

names.php
<?phptStatu/DocumenObjects/ValuetornvesDomain/Ip// app
/
```ph
}
```
;
    }  )      S)
EGORIE:VALID_CAT_keys(self:ay arr       ey),
    ($ktringelf::fromS=> sy)    fn($ke        ray_map(
  return ar
       y
    { all(): arratic functionta    public s }

';
    'document??] luethis->va[$n $icons      retur

        ];',
  academic-cap> 's' =ficate      'certile',
      => 'scace' governan           '
 paper', => 'newsdates'_up 'company           ator',
> 'calcults' =ocumen    'tax_d        art-bar',
> 'chrts' =ancial_repo   'fin
         ment-text', 'docuements' =>stment_agre  'inve          
 = [   $icons  {
     
  ): stringgetIcon(on ncti  public fu    }

  value];
->IES[$thisATEGORelf::VALID_C    return s   {
    ng
 bel(): striion lac funct   publi}

 ue;
    alhis->vn $t retur  {
       string
  ): e( valutionunc   public f
    }

 );elf($valuen new setur
        r      }
");
  $value}ry: {catego document on("InvalidtirgumentExcepInvalidArow new \          th) {
  S)D_CATEGORIE::VALIlfs($value, se_key_existarray   if (!    {
     ): self
ng $valueg(stritrinn fromSnctioic fuc statbli

    pu$value) {}te string uct(privanstrunction __coe fvat  pri  ];

,
    cates' 'Certifis' =>atetific 'cer       
overnance',' => 'Grnance   'govees',
     ny Updat=> 'Compa_updates' ny'compa,
        uments''Tax Doc' => documentsx_        'taorts',
RepFinancial ports' => 'ial_renanc       'fiments',
  Agreementest=> 'Invnts' ent_agreemeestminv   '
     TEGORIES = [ID_CAVALate const   priv
{
  goryentCateum

class Doc;tsueObjec\Valin\InvestorDomaace App\spamehp

nhp
<?ptegory.pocumentCaueObjects/Dtor/Valn/Inves/Domai app```php
//
```


    }
}[$i];units2) . ' ' . $d($bytes,  return roun             
     }
  
   1024;tes /=  $by    {
        +) - 1; $i+($units)< count&& $i 1024 ytes >  0; $b   for ($i =         
 '];
   'MB', 'GB'B', 'KB', s = [   $unit   leSize;
  fi $this->$bytes = {
          g
 strin(): zeeSiilrmattedFetFotion gic func    publ}

;
    IdorRoundnvestId === $istmentRound$this->inve return 

           }
    turn true; re        
    {ll)isibleToA>v  if ($this-   {
      
  boolndId):storRouor(int $inveeByInvestsiblon isAccestincblic fu   pu

 
    }t++;Counownload>d    $this-        {
d
ount(): voiownloadCementDon incrlic functi    pubss methods
/ Busine    /; }

untdCoownloan $this->d returunt(): int {adCoDownlogetn c functio
    publis->status; }rn $thiatus { retutStmentus(): DocuStaction get public fundId; }
   tRoun>investmenthis- { return $ndId(): ?inttRoustmentInvetion gepublic func }
    ToAll;siblern $this->vi{ retuool ToAll(): bisVisibleon uncti f
    public }>uploadedBy;is-$threturn { int oadedBy(): tUpl function geblicpu
    ate; }s->uploadDeturn $thible { rImmuta): DateTimee(Datploadtion getUic func    publeType; }
his->mimn $tretur string { eType(): getMimnction   public fu}
 e; leSizthis->fieturn $int { re(): n getFileSizctiounc f
    publiileName; }s->f$thig { return e(): strineNamtion getFilpublic func
    th; }Pas->filern $thitring { retuilePath(): sction getFpublic fun
    gory; }aten $this->cry { returategontCDocumetegory(): n getCac functio}
    publion; riptithis->desc { return $tring: scription()etDes function gicpuble; }
    this->titleturn $ring { r(): ston getTitleblic functipu>id; }
    s-eturn $thi { r): intgetId(ic function 
    publtters// Ge    

 0
    ) {}Count =nt $downloadte i  privas,
      statuntStatus $te Documeva  pri      ntRoundId,
$investmet rivate ?in     poAll,
   leTol $visibate bo    privedBy,
    pload$ute int       privaoadDate,
  uplmmutable $eTimeIte Datva     pri,
   $mimeTypeng ate stri      privileSize,
  $fnt  irivate   pme,
     $fileNaing ate str      privath,
  leP string $fi private,
       gorycatey $tegorocumentCa Divate     pron,
   scriptiing $devate str   prile,
     titate string $       priv int $id,
   private     truct(
 _consion _lic funct
{
    pubstorDocumentass Inve;

clbleeTimeImmutaus;
use DatDocumentStats\ectalueObjestor\V\Invinse App\Domagory;
uDocumentCatects\\ValueObjestorInvep\Domain\
use Ap
ities;Entr\n\InvestoDomaice App\

namespahp
<?phpocument.pvestorDies/Investor/Entitin/Inpp/Doma// a

```php
Layer 1. Domain 
####ation
lement Backend Imp
```

###-cap', 6);icdemcaons', 'aati confirmcates andent certifi', 'Investmcertificates,
(' 5)e',ents', 'scalce documgovernannutes and  mitingee', 'Board mvernancego 4),
('aper',wspneates', 'nd updments announcey aompanates', 'Ccompany_upd),
('lculator', 3caments', 'statedividend rms and , 'Tax fonts'ocume
('tax_d2),t-bar', 'charports', inancial reual f and annuarterlys', 'Qncial_report
('finaext', 1),cument-tdocuments', 'l dos and legaeementment agr 'Investts',menment_agree('invester) VALUES
, sort_ordtion, icondescripname, s (gorieocument_cater_dvestoinNSERT INTO ories
Iult categsert defa;

-- InAMP
)_TIMEST CURRENTULTMESTAMP DEFAed_at TI
    creatLT TRUE,DEFAUN e BOOLEAs_activT 0,
    iFAULr INT DEordet_),
    sorR(50HA VARCon icEXT,
   ption T   descriUNIQUE,
 NOT NULL AR(100)  name VARCH
   _INCREMENT,RY KEY AUTOBIGINT PRIMA    id (
ies nt_categordocumeE investor_
CREATE TABLceeferentegories rnt cacume

-- Do)
);accessed_at (accessed_atEX idx_
    INDd),ent_iocumtor_dd, invesount_itor_accnvest (itor_documeninvesDEX idx_    INents(id),
cumvestor_doEFERENCES inid) Rent__documEY (investor FOREIGN K(id),
   ountsr_accvestoENCES inEFERd) Rt_itor_accounvesIGN KEY (in
    FORE
    nt TEXT,ser_age    u(45),
RCHARddress VAp_aMP,
    i_TIMESTAT CURRENTTAMP DEFAULed_at TIMES   accessOT NULL,
 IGINT Nd Bment_icuestor_doinv
    LL,T NUIGINT NOid Bnt_estor_accou
    invMENT,TO_INCREY KEY AURIMARBIGINT Ps (
    id ment_accesstor_docuABLE inve
CREATE Tngrackiaccess tment 

-- Docudate)
);load_d_date (up idx_uploaNDEX),
    I (statusX idx_status),
    INDEategorytegory (cEX idx_cad),
    INDrounds(iinvestment_EFERENCES ound_id) Rnt_rY (investmeREIGN KE
    FOs(id),RENCES usered_by) REFE(uploadFOREIGN KEY 
        ESTAMP,
CURRENT_TIM ON UPDATE MPNT_TIMESTA CURREDEFAULTESTAMP t TIMupdated_aAMP,
    RENT_TIMESTFAULT CURSTAMP DEed_at TIME   creat
 ULT 0,EFAt INT Dad_counlo',
    downiveT 'actFAULCHAR(50) DEus VAR   statT NULL,
  BIGINd_idt_roun   investmenT TRUE,
 EAN DEFAUL BOOLo_allisible_tL,
    vGINT NOT NULoaded_by BI
    uplAMP,MEST_TILT CURRENTEFAUESTAMP Dd_date TIM uploa,
   OT NULLRCHAR(100) Ne_type VALL,
    mim NU INT NOTile_sizeL,
    f255) NOT NULHAR(name VARC
    file_NOT NULL,0) 50CHAR(ile_path VAR    f NOT NULL,
HAR(100) VARCategory
    cEXT,cription Tdes,
     NOT NULL55)ARCHAR(2e Vtitl
    CREMENT, AUTO_INEYRIMARY Kd BIGINT P
    ints (mer_docutoinvesREATE TABLE 
C tablesanagement mocument`sql
-- Da

``Schembase 
### Data
ely.em securto access thnvestors ents and ipload documdmins to u allowing at systememenment managdocuomplete ement a ciew
Impl Overvk 1)

###Weeem (agement Systcument Man 1: Do Phase
---

##gagement
d enency anor transparr investial fo Essent* HIGH -iority:*on  
**Prlementatimpady for Iatus:** Re  
**Stber 24, 2025** Novem

**Date:lanentation Pures ImplemFeatcal Critial - tor Portves In#   
             </button>
                <button
                  @click="deleteDocument(document.id)"
                  class="text-red-600 hover:text-red-700 text-sm"
                >
                  Delete
                </button>
              </div>
            </div>
            
            <div v-if="categoryData.documents.length === 0" class="px-6 py-8 text-center text-gray-500">
              <DocumentIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
              <p class="text-sm">No documents in this category</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <DocumentUploadModal
      v-if="showUploadModal"
      @close="showUploadModal = false"
      @uploaded="handleDocumentUploaded"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DocumentUploadModal from '@/components/Admin/DocumentUploadModal.vue';
import { PlusIcon, DocumentIcon } from '@heroicons/vue/24/outline';

// Component logic here...
</script>
```

#### 2. Enhanced Investor Documents Page

```vue
<!-- resources/js/pages/Investor/Documents.vue (Enhanced) -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <Link
              :href="route('investor.dashboard')"
              class="text-sm text-gray-600 hover:text-gray-900 flex items-center mb-2"
            >
              <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
              Back to Dashboard
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Investment Documents</h1>
            <p class="text-sm text-gray-600">Access your investment documents and reports</p>
          </div>
          <Link
            :href="route('investor.logout')"
            method="post"
            as="button"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Logout
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Search and Filter -->
      <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search documents..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <select
          v-model="selectedCategory"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category.value" :value="category.value">
            {{ category.label }}
          </option>
        </select>
      </div>

      <!-- Document Categories -->
      <div class="space-y-6">
        <div v-for="(categoryData, category) in filteredDocuments" :key="category" class="bg-white rounded-xl shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <component :is="getIconComponent(categoryData.category.icon)" class="h-5 w-5 mr-2 text-blue-600" aria-hidden="true" />
              {{ categoryData.category.label }}
              <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                {{ categoryData.documents.length }}
              </span>
            </h3>
          </div>
          
          <div class="divide-y divide-gray-200">
            <div v-for="document in categoryData.documents" :key="document.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                  <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <DocumentIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                  </div>
                  <div class="ml-4 flex-1">
                    <h4 class="text-sm font-medium text-gray-900">{{ document.title }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ document.description }}</p>
                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                      <span class="flex items-center">
                        <CalendarIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                        {{ formatDate(document.upload_date) }}
                      </span>
                      <span class="flex items-center">
                        <DocumentIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                        {{ document.formatted_file_size }}
                      </span>
                      <span class="flex items-center">
                        <ArrowDownTrayIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                        {{ document.download_count }} downloads
                      </span>
                    </div>
                  </div>
                </div>
                
                <div class="flex items-center gap-2">
                  <button
                    @click="previewDocument(document)"
                    class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                  >
                    <EyeIcon class="h-4 w-4 mr-1 inline" aria-hidden="true" />
                    Preview
                  </button>
                  <button
                    @click="downloadDocument(document.id)"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    <ArrowDownTrayIcon class="h-4 w-4 mr-1 inline" aria-hidden="true" />
                    Download
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="categoryData.documents.length === 0" class="px-6 py-12 text-center text-gray-500">
              <DocumentIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
              <p class="text-sm">No documents available in this category</p>
              <p class="text-xs mt-1">New documents will appear here when uploaded</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="Object.keys(filteredDocuments).length === 0" class="text-center py-12">
        <DocumentIcon class="h-16 w-16 mx-auto mb-4 text-gray-400" aria-hidden="true" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No documents found</h3>
        <p class="text-gray-600 mb-4">
          {{ searchQuery ? 'Try adjusting your search terms' : 'Documents will appear here when uploaded by administrators' }}
        </p>
        <button
          v-if="searchQuery"
          @click="clearSearch"
          class="text-blue-600 hover:text-blue-700 font-medium"
        >
          Clear search
        </button>
      </div>

      <!-- Help Section -->
      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex">
          <InformationCircleIcon class="h-6 w-6 text-blue-600 mr-3 flex-shrink-0" aria-hidden="true" />
          <div>
            <h4 class="text-sm font-semibold text-blue-900 mb-1">Need help accessing documents?</h4>
            <p class="text-sm text-blue-800 mb-3">
              If you're having trouble accessing or downloading documents, or need a specific document that's not listed, please contact our investor relations team.
            </p>
            <a
              href="mailto:investors@mygrownet.com"
              class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
            >
              Contact Investor Relations
              <ArrowRightIcon class="h-4 w-4 ml-1" aria-hidden="true" />
            </a>
          </div>
        </div>
      </div>
    </main>

    <!-- Document Preview Modal -->
    <DocumentPreviewModal
      v-if="previewDocument"
      :document="previewDocument"
      @close="previewDocument = null"
      @download="downloadDocument"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  ArrowLeftIcon,
  DocumentIcon,
  CalendarIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  InformationCircleIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline';

// Props and reactive data
const searchQuery = ref('');
const selectedCategory = ref('');
const previewDocument = ref(null);

// Computed properties for filtering
const filteredDocuments = computed(() => {
  // Filter logic here
});

// Methods
const downloadDocument = async (documentId: number) => {
  // Download implementation
};

const clearSearch = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
};
</script>
```

---

## Phase 2: Financial Reporting Dashboard (Week 2)

### Overview
Add comprehensive financial reporting and analytics to give investors transparency into company performance.

### Database Schema

```sql
-- Financial reports table
CREATE TABLE investor_financial_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    report_type VARCHAR(100) NOT NULL, -- 'quarterly', 'annual', 'monthly'
    report_period VARCHAR(50) NOT NULL, -- 'Q1 2025', '2024', 'Jan 2025'
    report_date DATE NOT NULL,
    total_revenue DECIMAL(15,2) NOT NULL,
    total_expenses DECIMAL(15,2) NOT NULL,
    net_profit DECIMAL(15,2) NOT NULL,
    gross_margin DECIMAL(5,2),
    operating_margin DECIMAL(5,2),
    net_margin DECIMAL(5,2),
    cash_flow DECIMAL(15,2),
    total_members INT,
    active_members INT,
    monthly_recurring_revenue DECIMAL(15,2),
    customer_acquisition_cost DECIMAL(10,2),
    lifetime_value DECIMAL(10,2),
    churn_rate DECIMAL(5,2),
    growth_rate DECIMAL(5,2),
    notes TEXT,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_report_type (report_type),
    INDEX idx_report_date (report_date),
    INDEX idx_published_at (published_at)
);

-- Company metrics snapshots (for historical tracking)
CREATE TABLE company_metrics_snapshots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    snapshot_date DATE NOT NULL,
    total_members INT NOT NULL,
    active_members INT NOT NULL,
    total_revenue DECIMAL(15,2) NOT NULL,
    monthly_revenue DECIMAL(15,2) NOT NULL,
    quarterly_revenue DECIMAL(15,2) NOT NULL,
    annual_revenue DECIMAL(15,2) NOT NULL,
    platform_valuation DECIMAL(15,2),
    market_cap DECIMAL(15,2),
    revenue_growth_rate DECIMAL(5,2),
    member_growth_rate DECIMAL(5,2),
    retention_rate DECIMAL(5,2),
    churn_rate DECIMAL(5,2),
    average_revenue_per_user DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_snapshot_date (snapshot_date),
    INDEX idx_snapshot_date (snapshot_date)
);

-- Revenue breakdown by source
CREATE TABLE revenue_breakdown (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    financial_report_id BIGINT NOT NULL,
    revenue_source VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    growth_rate DECIMAL(5,2),
    notes TEXT,
    
    FOREIGN KEY (financial_report_id) REFERENCES investor_financial_reports(id) ON DELETE CASCADE,
    INDEX idx_financial_report (financial_report_id),
    INDEX idx_revenue_source (revenue_source)
);

-- Insert sample revenue sources
INSERT INTO revenue_breakdown (financial_report_id, revenue_source, amount, percentage, growth_rate) VALUES
(1, 'subscription_fees', 64350.00, 45.0, 12.5),
(1, 'learning_packs', 35750.00, 25.0, 18.3),
(1, 'workshops_training', 21450.00, 15.0, 8.7),
(1, 'venture_builder_fees', 14300.00, 10.0, 25.2),
(1, 'other_services', 7150.00, 5.0, 5.1);
```

### Backend Implementation

#### 1. Domain Entities

```php
// app/Domain/Investor/Entities/FinancialReport.php
<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\ReportType;
use DateTimeImmutable;

class FinancialReport
{
    public function __construct(
        private int $id,
        private string $title,
        private ReportType $reportType,
        private string $reportPeriod,
        private DateTimeImmutable $reportDate,
        private float $totalRevenue,
        private float $totalExpenses,
        private float $netProfit,
        private ?float $grossMargin,
        private ?float $operatingMargin,
        private ?float $netMargin,
        private ?float $cashFlow,
        private ?int $totalMembers,
        private ?int $activeMembers,
        private ?float $monthlyRecurringRevenue,
        private ?float $customerAcquisitionCost,
        private ?float $lifetimeValue,
        private ?float $churnRate,
        private ?float $growthRate,
        private ?string $notes,
        private ?DateTimeImmutable $publishedAt,
        private array $revenueBreakdown = []
    ) {}

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getReportType(): ReportType { return $this->reportType; }
    public function getReportPeriod(): string { return $this->reportPeriod; }
    public function getReportDate(): DateTimeImmutable { return $this->reportDate; }
    public function getTotalRevenue(): float { return $this->totalRevenue; }
    public function getTotalExpenses(): float { return $this->totalExpenses; }
    public function getNetProfit(): float { return $this->netProfit; }
    public function getGrossMargin(): ?float { return $this->grossMargin; }
    public function getOperatingMargin(): ?float { return $this->operatingMargin; }
    public function getNetMargin(): ?float { return $this->netMargin; }
    public function getCashFlow(): ?float { return $this->cashFlow; }
    public function getTotalMembers(): ?int { return $this->totalMembers; }
    public function getActiveMembers(): ?int { return $this->activeMembers; }
    public function getMonthlyRecurringRevenue(): ?float { return $this->monthlyRecurringRevenue; }
    public function getCustomerAcquisitionCost(): ?float { return $this->customerAcquisitionCost; }
    public function getLifetimeValue(): ?float { return $this->lifetimeValue; }
    public function getChurnRate(): ?float { return $this->churnRate; }
    public function getGrowthRate(): ?float { return $this->growthRate; }
    public function getNotes(): ?string { return $this->notes; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function getRevenueBreakdown(): array { return $this->revenueBreakdown; }

    // Business methods
    public function isPublished(): bool
    {
        return $this->publishedAt !== null;
    }

    public function getProfitMargin(): float
    {
        return $this->totalRevenue > 0 ? ($this->netProfit / $this->totalRevenue) * 100 : 0;
    }

    public function getRevenueGrowthStatus(): string
    {
        if ($this->growthRate === null) return 'unknown';
        if ($this->growthRate > 10) return 'excellent';
        if ($this->growthRate > 5) return 'good';
        if ($this->growthRate > 0) return 'positive';
        return 'negative';
    }

    public function getFinancialHealthScore(): int
    {
        $score = 0;
        
        // Profitability (40 points)
        if ($this->netProfit > 0) $score += 40;
        elseif ($this->netProfit > -($this->totalRevenue * 0.1)) $score += 20;
        
        // Growth (30 points)
        if ($this->growthRate > 15) $score += 30;
        elseif ($this->growthRate > 10) $score += 25;
        elseif ($this->growthRate > 5) $score += 20;
        elseif ($this->growthRate > 0) $score += 10;
        
        // Margins (30 points)
        $netMargin = $this->getNetMargin() ?? 0;
        if ($netMargin > 20) $score += 30;
        elseif ($netMargin > 15) $score += 25;
        elseif ($netMargin > 10) $score += 20;
        elseif ($netMargin > 5) $score += 15;
        elseif ($netMargin > 0) $score += 10;
        
        return min(100, $score);
    }
}
```

#### 2. Service Layer

```php
// app/Domain/Investor/Services/FinancialReportingService.php
<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\FinancialReport;
use App\Domain\Investor\Repositories\FinancialReportRepositoryInterface;
use App\Domain\Investor\ValueObjects\ReportType;

class FinancialReportingService
{
    public function __construct(
        private FinancialReportRepositoryInterface $reportRepository
    ) {}

    public function getLatestReports(int $limit = 5): array
    {
        return $this->reportRepository->findLatestPublished($limit);
    }

    public function getReportsByType(ReportType $type): array
    {
        return $this->reportRepository->findByType($type);
    }

    public function getFinancialSummary(): array
    {
        $latestReport = $this->reportRepository->findLatestPublished(1)[0] ?? null;
        
        if (!$latestReport) {
            return $this->getDefaultSummary();
        }

        return [
            'latest_period' => $latestReport->getReportPeriod(),
            'total_revenue' => $latestReport->getTotalRevenue(),
            'net_profit' => $latestReport->getNetProfit(),
            'profit_margin' => $latestReport->getProfitMargin(),
            'growth_rate' => $latestReport->getGrowthRate(),
            'health_score' => $latestReport->getFinancialHealthScore(),
            'total_members' => $latestReport->getTotalMembers(),
            'mrr' => $latestReport->getMonthlyRecurringRevenue(),
            'revenue_breakdown' => $latestReport->getRevenueBreakdown(),
        ];
    }

    public function getPerformanceMetrics(): array
    {
        $reports = $this->reportRepository->findLatestPublished(4);
        
        if (empty($reports)) {
            return $this->getDefaultMetrics();
        }

        return [
            'revenue_trend' => $this->calculateRevenueTrend($reports),
            'profit_trend' => $this->calculateProfitTrend($reports),
            'member_growth' => $this->calculateMemberGrowth($reports),
            'margin_analysis' => $this->calculateMarginAnalysis($reports),
        ];
    }

    private function calculateRevenueTrend(array $reports): array
    {
        return [
            'labels' => array_map(fn($r) => $r->getReportPeriod(), array_reverse($reports)),
            'data' => array_map(fn($r) => $r->getTotalRevenue(), array_reverse($reports)),
        ];
    }

    private function calculateProfitTrend(array $reports): array
    {
        return [
            'labels' => array_map(fn($r) => $r->getReportPeriod(), array_reverse($reports)),
            'data' => array_map(fn($r) => $r->getNetProfit(), array_reverse($reports)),
        ];
    }

    private function calculateMemberGrowth(array $reports): array
    {
        return [
            'labels' => array_map(fn($r) => $r->getReportPeriod(), array_reverse($reports)),
            'data' => array_map(fn($r) => $r->getTotalMembers(), array_reverse($reports)),
        ];
    }

    private function calculateMarginAnalysis(array $reports): array
    {
        $latest = $reports[0];
        $previous = $reports[1] ?? null;
        
        $currentMargin = $latest->getProfitMargin();
        $previousMargin = $previous ? $previous->getProfitMargin() : 0;
        
        return [
            'current_margin' => $currentMargin,
            'previous_margin' => $previousMargin,
            'margin_change' => $currentMargin - $previousMargin,
            'trend' => $currentMargin > $previousMargin ? 'improving' : 'declining',
        ];
    }

    private function getDefaultSummary(): array
    {
        return [
            'latest_period' => 'No data',
            'total_revenue' => 0,
            'net_profit' => 0,
            'profit_margin' => 0,
            'growth_rate' => 0,
            'health_score' => 0,
            'total_members' => 0,
            'mrr' => 0,
            'revenue_breakdown' => [],
        ];
    }

    private function getDefaultMetrics(): array
    {
        return [
            'revenue_trend' => ['labels' => [], 'data' => []],
            'profit_trend' => ['labels' => [], 'data' => []],
            'member_growth' => ['labels' => [], 'data' => []],
            'margin_analysis' => [
                'current_margin' => 0,
                'previous_margin' => 0,
                'margin_change' => 0,
                'trend' => 'stable',
            ],
        ];
    }
}
```

---

## Phase 3: Communication & Announcements System (Week 2-3)

### Database Schema

```sql
-- Investor announcements
CREATE TABLE investor_announcements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    summary VARCHAR(500),
    category VARCHAR(100) NOT NULL, -- 'company_update', 'financial', 'product', 'governance'
    priority VARCHAR(50) DEFAULT 'normal', -- 'low', 'normal', 'high', 'urgent'
    published_at TIMESTAMP NULL,
    scheduled_at TIMESTAMP NULL,
    author_id BIGINT NOT NULL,
    visible_to_all BOOLEAN DEFAULT TRUE,
    investment_round_id BIGINT NULL,
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (investment_round_id) REFERENCES investment_rounds(id),
    INDEX idx_published_at (published_at),
    INDEX idx_category (category),
    INDEX idx_priority (priority)
);

-- Investor notifications
CREATE TABLE investor_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    investor_account_id BIGINT NOT NULL,
    type VARCHAR(100) NOT NULL, -- 'announcement', 'document', 'financial_report', 'dividend'
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON, -- Additional data for the notification
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (investor_account_id) REFERENCES investor_accounts(id) ON DELETE CASCADE,
    INDEX idx_investor_account (investor_account_id),
    INDEX idx_type (type),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at)
);

-- Email notification preferences
CREATE TABLE investor_notification_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    investor_account_id BIGINT NOT NULL,
    email_announcements BOOLEAN DEFAULT TRUE,
    email_financial_reports BOOLEAN DEFAULT TRUE,
    email_documents BOOLEAN DEFAULT TRUE,
    email_dividends BOOLEAN DEFAULT TRUE,
    email_governance BOOLEAN DEFAULT TRUE,
    frequency VARCHAR(50) DEFAULT 'immediate', -- 'immediate', 'daily', 'weekly'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (investor_account_id) REFERENCES investor_accounts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_investor_preferences (investor_account_id)
);
```

### Frontend Enhancement - Enhanced Dashboard

```vue
<!-- resources/js/pages/Investor/Dashboard.vue (Enhanced with Financial Data) -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header (existing) -->
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Banner (existing) -->
      
      <!-- Investment Summary Card (existing) -->
      
      <!-- Financial Performance Section (NEW) -->
      <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Company Financial Performance</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Financial Summary Card -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Latest Financial Summary</h3>
            
            <div v-if="financialSummary" class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Reporting Period</span>
                <span class="font-semibold text-gray-900">{{ financialSummary.latest_period }}</span>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-3">
                  <p class="text-xs text-blue-600 font-medium">Total Revenue</p>
                  <p class="text-lg font-bold text-blue-900">K{{ formatNumber(financialSummary.total_revenue) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                  <p class="text-xs text-green-600 font-medium">Net Profit</p>
                  <p class="text-lg font-bold text-green-900">K{{ formatNumber(financialSummary.net_profit) }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs text-gray-500">Profit Margin</p>
                  <p class="text-sm font-semibold text-gray-900">{{ financialSummary.profit_margin.toFixed(1) }}%</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Growth Rate</p>
                  <p class="text-sm font-semibold" :class="financialSummary.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ financialSummary.growth_rate >= 0 ? '+' : '' }}{{ financialSummary.growth_rate.toFixed(1) }}%
                  </p>
                </div>
              </div>
              
              <!-- Financial Health Score -->
              <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-xs text-gray-600">Financial Health Score</span>
                  <span class="text-sm font-bold text-gray-900">{{ financialSummary.health_score }}/100</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="h-2 rounded-full transition-all"
                    :class="getHealthScoreColor(financialSummary.health_score)"
                    :style="{ width: `${financialSummary.health_score}%` }"
                  ></div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-6 text-gray-500">
              <ChartBarIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
              <p class="text-sm">Financial data will be available after the first quarterly report</p>
            </div>
          </div>
          
          <!-- Revenue Breakdown Chart -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Breakdown</h3>
            
            <div v-if="financialSummary?.revenue_breakdown?.length" class="space-y-3">
              <div v-for="source in financialSummary.revenue_breakdown" :key="source.source" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-3" :style="{ backgroundColor: getRevenueSourceColor(source.source) }"></div>
                  <span class="text-sm text-gray-700">{{ formatRevenueSource(source.source) }}</span>
                </div>
                <div class="text-right">
                  <p class="text-sm font-semibold text-gray-900">{{ source.percentage }}%</p>
                  <p class="text-xs text-gray-500">K{{ formatNumber(source.amount) }}</p>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-6 text-gray-500">
              <PieChartIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
              <p class="text-sm">Revenue breakdown will be available with financial reports</p>
            </div>
          </div>
        </div>
        
        <!-- Performance Charts -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Trends</h3>
          
          <div v-if="performanceMetrics" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Trend Chart -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-3">Revenue Trend</h4>
              <div class="h-48">
                <canvas ref="revenueTrendChart"></canvas>
              </div>
            </div>
            
            <!-- Member Growth Chart -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-3">Member Growth</h4>
              <div class="h-48">
                <canvas ref="memberGrowthChart"></canvas>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500">
            <ChartBarIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
            <p class="text-sm">Performance trends will be available after multiple reporting periods</p>
          </div>
        </div>
      </div>
      
      <!-- Recent Announcements Section (NEW) -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-900">Recent Updates</h2>
          <Link
            :href="route('investor.announcements')"
            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
          >
            View All →
          </Link>
        </div>
        
        <div v-if="recentAnnouncements?.length" class="space-y-4">
          <div v-for="announcement in recentAnnouncements" :key="announcement.id" class="bg-white rounded-lg shadow p-4">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span :class="getAnnouncementCategoryClass(announcement.category)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ formatAnnouncementCategory(announcement.category) }}
                  </span>
                  <span v-if="announcement.priority === 'high'" class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                    Important
                  </span>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ announcement.title }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ announcement.summary }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(announcement.published_at) }}</p>
              </div>
              <button
                @click="viewAnnouncement(announcement)"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium ml-4"
              >
                Read More
              </button>
            </div>
          </div>
        </div>
        
        <div v-else class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
          <SpeakerWaveIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
          <p class="text-sm">No recent announcements</p>
          <p class="text-xs mt-1">Company updates will appear here</p>
        </div>
      </div>
      
      <!-- Existing sections (Investment Round, Platform Metrics, etc.) -->
      
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import { 
  ChartBarIcon, 
  PieChartIcon, 
  SpeakerWaveIcon 
} from '@heroicons/vue/24/outline';

Chart.register(...registerables);

// Enhanced props
interface Props {
  investor: Investor;
  investmentMetrics?: InvestmentMetrics;
  round?: InvestmentRound | null;
  platformMetrics?: PlatformMetrics;
  financialSummary?: FinancialSummary; // NEW
  performanceMetrics?: PerformanceMetrics; // NEW
  recentAnnouncements?: Announcement[]; // NEW
}

const props = defineProps<Props>();

// Chart refs
const revenueTrendChart = ref<HTMLCanvasElement | null>(null);
const memberGrowthChart = ref<HTMLCanvasElement | null>(null);

// Methods for financial data
const getHealthScoreColor = (score: number): string => {
  if (score >= 80) return 'bg-green-500';
  if (score >= 60) return 'bg-yellow-500';
  if (score >= 40) return 'bg-orange-500';
  return 'bg-red-500';
};

const getRevenueSourceColor = (source: string): string => {
  const colors = {
    'subscription_fees': '#3b82f6',
    'learning_packs': '#10b981',
    'workshops_training': '#f59e0b',
    'venture_builder_fees': '#8b5cf6',
    'other_services': '#6b7280',
  };
  return colors[source] || '#6b7280';
};

const formatRevenueSource = (source: string): string => {
  const labels = {
    'subscription_fees': 'Subscription Fees',
    'learning_packs': 'Learning Packs',
    'workshops_training': 'Workshops & Training',
    'venture_builder_fees': 'Venture Builder',
    'other_services': 'Other Services',
  };
  return labels[source] || source;
};

// Initialize charts on mount
onMounted(() => {
  if (props.performanceMetrics) {
    initializeCharts();
  }
});

const initializeCharts = () => {
  // Revenue trend chart
  if (revenueTrendChart.value && props.performanceMetrics?.revenue_trend) {
    new Chart(revenueTrendChart.value, {
      type: 'line',
      data: {
        labels: props.performanceMetrics.revenue_trend.labels,
        datasets: [{
          label: 'Revenue (K)',
          data: props.performanceMetrics.revenue_trend.data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (value) => `K${value.toLocaleString()}` }
          }
        }
      }
    });
  }
  
  // Member growth chart
  if (memberGrowthChart.value && props.performanceMetrics?.member_growth) {
    new Chart(memberGrowthChart.value, {
      type: 'bar',
      data: {
        labels: props.performanceMetrics.member_growth.labels,
        datasets: [{
          label: 'Members',
          data: props.performanceMetrics.member_growth.data,
          backgroundColor: '#10b981',
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (value) => value.toLocaleString() }
          }
        }
      }
    });
  }
};
</script>
```

This implementation plan provides:

1. **Complete Document Management System** - File uploads, categorization, secure access
2. **Financial Reporting Dashboard** - Comprehensive financial metrics and trends
3. **Communication System** - Announcements, notifications, and investor updates
4. **Enhanced Analytics** - Performance charts, health scores, and trend analysis

The architecture follows your existing domain-driven design patterns and integrates seamlessly with the current investor portal structure. Each phase builds upon the previous one, ensuring a smooth implementation process.