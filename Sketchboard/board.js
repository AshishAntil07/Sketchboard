// functions

const create = (elem, ns=null) => ns ? document.createElementNS('http://www.w3.org/2000/svg', elem) : document.createElement(elem);
const getStyle = (elem, prop) => Number(getComputedStyle(elem)[prop].replace('px', ''));
const resize = (elem, isTextBox) => {
  const container = document.createElement('div');
  container.classList.add('resizeCont');
  isTextBox?container.style.height = 'auto':0;

  const resizeCombos = [
    e=>{resizeFunctions.left(e, container); resizeFunctions.top(e, container)},
    e=>resizeFunctions.top(e, container),
    e=>{resizeFunctions.top(e, container); resizeFunctions.right(e, container)},
    e=>resizeFunctions.left(e, container),
    e=>resizeFunctions.right(e, container),
    e=>{resizeFunctions.left(e, container); resizeFunctions.bottom(e, container)},
    e=>resizeFunctions.bottom(e, container),
    e=>{resizeFunctions.bottom(e, container); resizeFunctions.right(e, container)},
  ]

  let top, left;
  const dragFunc = e => {
    container.style.top = (e.clientY-top)<getStyle(topBar, 'height')
      ?getStyle(topBar, 'height')
      :(e.clientY-top)+'px';
    container.style.left = (e.clientX-left)<getStyle(sideBar, 'width') && right>getStyle(rightSideBar, 'width')
      ?getStyle(sideBar, 'width')
      :(e.clientX-left)+'px';
  }
  container.addEventListener('mousedown', e => {
    if(!(buttons.includes(e.target))){
      top = e.clientY - getStyle(container, 'top');
      left = e.clientX - getStyle(container, 'left');
      right = e.clientX + getStyle(container, 'width') - window.innerWidth;
      document.addEventListener('mousemove', dragFunc);
      document.addEventListener('mouseup', e => {
        document.removeEventListener('mousemove', dragFunc);
      }, {once:true});
    }
  })

  const rotater = create('div');
  rotater.classList.add('rotater');
  rotater.onmousedown = e => {
    e.stopPropagation();

    // Cursor

    const cursor = create('div');
    cursor.setAttribute('style', `
      height: 20px;
      width: 20px;
      background-image: url('Pictures/rotateCursor.png');
      background-repeat: no-repeat;
      background-size: contain;
      background-position: center;
      position: absolute;
      top: ${(e.clientY-(getStyle(cursor, 'height')/2))}px;
      left: ${(e.clientX-(getStyle(cursor, 'width')/2))}px;
    `)
    document.body.append(cursor);
    document.documentElement.style.cursor = 'none';
    document.onmousemove = e => {
      cursor.style.top = (e.clientY-(getStyle(cursor, 'height')/2))+'px';
      cursor.style.left = (e.clientX-(getStyle(cursor, 'width')/2))+'px'; 

      // Rotation

      const x = container.getBoundingClientRect().x + container.getBoundingClientRect().width/2;
      const y = container.getBoundingClientRect().y + container.getBoundingClientRect().height/2;
      container.style.transform = `rotate(${(Math.abs((Math.atan2(e.clientX-x, e.clientY-y)*180/Math.PI * -1) + 180))}deg)`;
      console.log(Math.abs(Math.atan2(e.clientX - x, e.clientY - y)*180/Math.PI));
    }
    document.onmouseup = e => {
      cursor.remove();
      document.onmousemove = () => {}
      document.documentElement.style.cursor = 'auto';
    }
  }


  const resizeElem = create('div');
  resizeElem.setAttribute('style', `
    border: 1px dashed blue;
    display: flex;
    flex-flow: column nowrap;
    justify-content: space-between;
    position: absolute;
    width: 100%;
    height: 100%;
  `)
  isTextBox?resizeElem.addEventListener('dblclick', e=>elem.focus()):0;
  

  const floors = [
    create('div'),
    create('div'),
    create('div'),
  ];
  floors.forEach(elem => {
    elem.setAttribute('style', `
      display: flex;
      justify-content: space-between;
      width: 100%;
    `)
    resizeElem.append(elem);
  })

  let buttons = [];

  for(let i=0; i<8; i++){
    const resizeBtn = create('div');
    resizeBtn.style.height = resizeBtn.style.width = '5px';
    resizeBtn.style.background = 'blue';
    buttons.push(resizeBtn);

    if(i < 3){
      floors[0].append(resizeBtn)
      if(i===0){
        resizeBtn.style.transform = 'translate(-2.5px, -2.5px)';
        resizeBtn.style.cursor = 'nw-resize';
      }else if(i===2){
        resizeBtn.style.transform = 'translate(2.5px, -2.5px)';
        resizeBtn.style.cursor = 'ne-resize';
      }else{
        resizeBtn.style.transform = 'translate(0, -2.5px)';
        resizeBtn.style.cursor = 'n-resize';
      }
    }else if(i < 5){
      floors[1].append(resizeBtn)
      if(i===3){
        resizeBtn.style.transform = 'translate(-2.5px, 0)';
        resizeBtn.style.cursor = 'w-resize';
      }else{
        resizeBtn.style.transform = 'translate(2.5px, 0)';
        resizeBtn.style.cursor = 'e-resize';
      };
    }else{
      floors[2].append(resizeBtn)
      if(i===5){
        resizeBtn.style.transform = 'translate(-2.5px, 2.5px)';
        resizeBtn.style.cursor = 'sw-resize';
      }else if(i===7){
        resizeBtn.style.transform = 'translate(2.5px, 2.5px)';
        resizeBtn.style.cursor = 'se-resize';
      }else{
        resizeBtn.style.transform = 'translate(0, 2.5px)';
        resizeBtn.style.cursor = 's-resize';
      }
    };
  }
  buttons.forEach((btn, index) => {
    btn.addEventListener('mousedown', e=>{
      document.addEventListener('mousemove', resizeCombos[index])
      document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', resizeCombos[index], {once:true}))
    });
  })
  container.append(rotater, resizeElem, elem);
}

const resizeFunctions = {
  top: (e, elem) => {
    const height = getStyle(elem, 'height');
    const difference = getStyle(elem, 'top') - e.clientY;
    elem.style.top = e.clientY+'px';
    elem.style.height = (difference > 0 ? height + Math.abs(difference) : height - Math.abs(difference))+'px';
  },
  left: (e, elem) => {
    if(e.clientX > getStyle(sideBar, 'width')){
      const width = getStyle(elem, 'width');
      const difference = getStyle(elem, 'left') - e.clientX;
      elem.style.left = e.clientX+'px';
      elem.style.width = (difference > 0 ? width + Math.abs(difference) : width - Math.abs(difference))+'px';
    }
  },
  bottom: (e, elem) => {
    elem.style.height = (e.clientY - getStyle(elem, 'top'))+'px';
  },
  right: (e, elem) => {
    elem.style.width = (e.clientX - getStyle(elem, 'left'))+'px';
  }
}

const childAdded = () => {
  const lastElem = board.lastChild;
  const child = lastElem.cloneNode(true);

  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.append(child);
  svg.style.height = svg.style.width = '100%';
  svg.style.backgroundColor = getComputedStyle(board).backgroundColor=='rgba(0, 0, 0, 0)'?'white':getComputedStyle(board).backgroundColor;
  svg.setAttribute('viewBox', `0 0 ${getStyle(board, 'width')} ${getStyle(board, 'height')}`);

  const container = document.createElement('div');
  container.classList.add('element');
  container.addEventListener('mouseover', e=>{
    lastElem.style.outline = '1px solid blue'
    lastElem.style.strokeWidth = getStyle(lastElem, 'stroke-width')+2+'px';
  });
  container.addEventListener('mouseout', e=>{
    lastElem.style.outline = 'none';
    lastElem.style.strokeWidth = getStyle(lastElem, 'stroke-width')-2+'px';
  });
  elements.append(container);

  const deleteBtn = document.createElement('div');
  deleteBtn.innerHTML = '&Cross;';
  deleteBtn.setAttribute('style', `
    height: 15px;
    width: 15px;
    font-size: 20px;
    position: relative;
    top: 10px;
    cursor: pointer;
  `)
  deleteBtn.title = 'Delete';
  deleteBtn.addEventListener('click', e => {
    lastElem.remove();
    container.remove();
  });

  container.append(deleteBtn, svg);
  elements.style.height = `${elements.scrollHeight + 10}px`;
  elements.scrollTop = elements.scrollHeight;
}



// Top Bar

const topBar = create('div');
topBar.classList.add('topBar');
topBar.setAttribute('style', `
  height: 50px;
  background: darkgray;
  border-bottom: 3px solid lightgray;
  display: flex;
  justify-content: right;
  align-items: center;
  padding-right: 10%;
  box-sizing: border-box;
`)

const undoBtn = create('button');
undoBtn.innerHTML = 'Undo';
undoBtn.addEventListener('click', e => {board.lastChild?.remove(); elements.lastChild?.remove()});

const importBtn = create('button');
importBtn.innerHTML = 'Import';
const fileBtn = create('input');
fileBtn.type = 'file';
importBtn.addEventListener('click', e => fileBtn.click());
fileBtn.addEventListener('input', e => {
  if(fileBtn.files[0].type === 'image/svg+xml'){
    const reader = new FileReader();
    reader.onload = e => {
      boardParent.innerHTML = e.target.result;
      board = boardParent.children[0]
      board.style.width = board.style.height = '100%';
      addListener(board)
    }
    reader.readAsText(fileBtn.files[0]);
  }else{
    alert('Only svg files are allowed.');
  }
})

const exportBtn = create('button');
exportBtn.innerHTML = 'Export';


const anchor = create('a');
exportBtn.addEventListener('click', () => {
  anchor.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(boardParent.innerHTML));
  anchor.setAttribute('download', 'sketch'+parseInt(Math.random()*10000)+'.svg')
  anchor.click();
})

const penWidth = create('input');
penWidth.type = 'number';
penWidth.min = penWidth.value = 1;
penWidth.max = 50;
penWidth.classList.add('pen-width');
penWidth.addEventListener('change', e => e.target.value === '' ? e.target.value = 1 : 0)

const colorMain = create('div');
colorMain.classList.add('color');
colorMain.title = 'Black';

const colorCont = create('div');
colorCont.setAttribute('style', `
  margin-top: 35px;
  margin-left: 50%;
  transform: translate(-50%, 0);
`)
colorCont.classList.add('container');

const colors = ['Brown', 'Red', 'Green', 'Black', 'Blue', 'Yellow', 'Pink', 'Purple', 'Orange', 'Skyblue'];
colors.forEach(elem => {
  const color = create('div');
  color.style.backgroundColor = color.title = elem;
  color.style.margin = '2.5px';
  color.addEventListener('click', e => {
    colorMain.style.backgroundColor = colorMain.title = elem;
    colorCont.style.display = 'none';
  })
  colorCont.append(color);
})

colorCont.addEventListener('focusout', e => colorCont.style.display = 'none');
colorMain.addEventListener('click', e => !colorCont.contains(e.target)?colorCont.style.display = 'flex':0);

colorMain.append(colorCont)

topBar.append(colorMain, penWidth, undoBtn, importBtn, exportBtn);
document.body.append(topBar);


// Side Bar

const sideBar = create('div');
sideBar.classList.add('sideBar');
sideBar.setAttribute('style', `
  width: 50px;
  height: ${window.innerHeight-50}px;
  background: lightgray;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-flow: column wrap;
`)

const textBtn = create('div');
textBtn.title = 'Add textbox.';
textBtn.style.backgroundImage = 'url("Pictures/Text-cursor.png")';
textBtn.addEventListener('click', e => {
  const textbox = create('textarea');
  textbox.setAttribute('style', `
    width: 100%;
    height: 100%;
    background: transparent;
    outline: none;
    border: none;
    resize: none;
  `)
  textbox.addEventListener('scroll', e => {
    textbox.style.height = textbox.scrollHeight+'px';
  })
  resize(textbox, true);

  const has = (elemList, elem) => {for(let i=0;i<elemList.length;i++) if(elemList[i]==elem) return true; return false}

  const textAdder = e => {
    if(!has(textbox.parentElement.getElementsByTagName('*'), e.target)){
      if(textbox.value.trim() !== ''){
        const style = getComputedStyle(textbox);
        const object = create('foreignObject', true);
        const textElem = create('div');
        textElem.innerHTML = textbox.value;
        object.setAttribute('x', (getStyle(textbox.parentElement, 'left')-getStyle(sideBar, 'width')+2)+'px');
        object.setAttribute('y', (getStyle(textbox.parentElement, 'top')-getStyle(topBar, 'height')+2)+'px');
        object.setAttribute('style', `
          height: ${textbox.scrollHeight}px;
          transform: ${getComputedStyle(textbox.parentElement).transform};
          width: ${style.width};
        `)

        textElem.setAttribute('style', `
          font-size: ${style.fontSize};
          color: ${style.color};
          font-family: ${style.fontFamily};
          width: 100%;
          height: 100%;
          overflow-wrap: break-word;
          -webkit-user-select: none;
          -moz-user-select: none;
          -ms-user-select: none;
          user-select: none;
        `);
        object.append(textElem)
        board.append(object);
        childAdded();
      }
      textbox.parentElement.remove();
      document.removeEventListener('mousedown', textAdder)
    }
  }

  document.addEventListener('mousedown', textAdder)
  boardParent.prepend(textbox.parentElement);
  textbox.focus()
})

const lineBtn = create('div');
lineBtn.title = 'Shapes';
lineBtn.style.backgroundImage = 'url("Pictures/zigZag.png")';
lineBtn.addEventListener('click', e => {
  let points = [];                                         // point elements... array, not elementList. for line elements. also display coordinates.
  let coordinates = [];
  removeListener(board);
  const line = create('polyline', true);
  line.setAttribute('style', `
    fill: transparent;
    stroke-linejoin: round;
    stroke-linecap: round;
    stroke: ${getComputedStyle(colorMain).backgroundColor};
    stroke-width: ${penWidth.value+'px'};
  `)
  line.setAttribute('points', '');
  const downFunc = e => {
    if(e.target !== save && e.target !== discard && e.clientX > getStyle(sideBar, 'width') && e.clientX < window.innerWidth-getStyle(rightSideBar, 'width') && e.clientY > getStyle(topBar, 'height')){
      const currPoint = `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `;
      const prevPoints = line.getAttribute('points');
      line.setAttribute('points', prevPoints+currPoint);

      const adjustPoints = e => {
        styleString = `
          left: ${e.clientX}px;
          top: ${e.clientY}px;
        `;
        point.setAttribute('style', styleString);
        coordinate.innerHTML = `Left: ${e.clientX} \nTop: ${e.clientY}`;
        coordinate.setAttribute('style', styleString)
      }
      let styleString = `
        left: ${e.clientX}px;
        top: ${e.clientY}px;
      `;
      const point = document.createElement('div');
      point.classList.add('point');
      point.setAttribute('style', styleString);
      point.addEventListener('mousedown', e => {
        const pointMoveFunc = e => {
          const pointIndex = points.indexOf(point);
          const pointsArr = line.getAttribute('points').split(' ');
          pointsArr.splice(pointIndex, 1, `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')}`);
          line.setAttribute('points', pointsArr.join(' '));
          adjustPoints(e);
        }
        document.addEventListener('mousemove', pointMoveFunc);
        document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', pointMoveFunc), {once:true});
      })
      points.push(point);

      const coordinate = document.createElement('pre');
      coordinate.classList.add('coordinates');
      coordinate.innerHTML = `Left: ${e.clientX} \nTop: ${e.clientY}`;
      coordinate.setAttribute('style', styleString)

      document.body.append(point, coordinate);
      coordinates.push(coordinate);

      const moveFunc = e => {
        line.setAttribute('points', prevPoints+`${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `)
        adjustPoints(e);
      };
      document.addEventListener('mousemove', moveFunc)
      document.addEventListener('mouseup', e=>document.removeEventListener('mousemove', moveFunc), {once:true});
    }
  }
  board.addEventListener('mousedown', downFunc);
  popUp.style.display = 'flex';
  function rem(){
    coordinates.forEach(elem => elem.remove());
    points.forEach(elem => elem.remove());
    board.removeEventListener('mousedown', downFunc);
    addListener(board);
  }
  popUp.save(e => {
    rem();
    childAdded();
  })
  popUp.discard(e => {
    rem();
    line.remove();
  })

  board.append(line)
});

const imageBtn = create('div');
imageBtn.title = 'Insert an Image.';
imageBtn.style.backgroundImage = 'url("Pictures/Image-icon.webp")';

const laserBtn = create('div');
laserBtn.title = 'Laser';
laserBtn.style.backgroundImage = 'url("Pictures/Laser.png")';
laserBtn.style.backgroundSize = '35px 15px';

sideBar.append(textBtn, lineBtn, imageBtn, laserBtn);


// Right Side Bar

const rightSideBar = create('div');
rightSideBar.setAttribute('style', `
  height: ${window.innerHeight-50}px;
  width: 210px;
  padding-top: 10px;
  padding-left: 10px;
  background: lightgray;
  display: flex;
  flex-flow: column nowrap;
  box-sizing: border-box;
`)

const heading = create('div');
heading.setAttribute('style', `
  display: flex;
  justify-content: center;
  align-items: flex-start;
  font-size: 20px;
  height: 25px;
  width: 100%;
`)
heading.innerHTML = 'Elements';

const elements = create('div');
elements.setAttribute('style', `
  width: 100%;
  overflow-y: auto;
`)
elements.classList.add('elementContainer');

rightSideBar.append(heading, elements);


// Board

const boardParent = create('div');
boardParent.style.height = `${window.innerHeight - getStyle(topBar, 'height')}px`;
boardParent.style.width = `${window.innerWidth - getStyle(sideBar, 'width')-getStyle(rightSideBar, 'width')}px`;

let board = create('svg', true);
board.classList.add('board');

const drawFunc = e => {
  if(board.lastChild.tagName == 'polyline'){
    const currPoint = `${e.clientX-getStyle(sideBar, 'width')},${e.clientY - getStyle(topBar, 'height')} `;
    const prevPoints = board.lastChild.getAttribute('points');
    board.lastChild.setAttribute('points', prevPoints+currPoint);
  }
}

const downFunc = e => {
  const line = create('polyline', true);
  line.setAttribute('style', `
    fill: transparent;
    stroke-linejoin: round;
    stroke-linecap: round;
    stroke: ${getComputedStyle(colorMain).backgroundColor};
    stroke-width: ${penWidth.value+'px'};
  `)
  line.setAttribute('points', '');
  board.addEventListener('mousemove', drawFunc)
  document.addEventListener('mouseup', upFunc, {once:true})
  board.append(line);
}

const upFunc = e => {
  board.removeEventListener('mousemove', drawFunc);
  if(board.lastChild.tagName == 'polyline'){
    const points = board.lastChild?.getAttribute('points')?.trim();
    if(points) points.split(' ').length < 2 ? board.lastChild.remove() : childAdded();
  }
}

const addListener = elem => elem.addEventListener('mousedown', downFunc);
const removeListener = elem => elem.removeEventListener('mousedown', downFunc)


addListener(board);


boardParent.append(board);
const paarent = create('div');
paarent.style.display = 'flex';
paarent.style.flexShrink = '0';

paarent.append(sideBar, boardParent, rightSideBar);

const popUp = create('div');
popUp.setAttribute('style', `
  height: 70px;
  width: 100%;
  position: absolute;
  bottom: 0;
  display: none;
  justify-content: center;
`)

const save = create('div');
save.style.background = 'lightgreen';
save.classList.add('popUpButton');
save.innerHTML = '&check;';

const discard = create('div');
discard.style.background = 'red';
discard.classList.add('popUpButton');
discard.innerHTML = '&cross;'

buttons = ['discard', 'save'];
for(let i=0; i<buttons.length; i++)
  popUp[buttons[i]] = func => popUp.children[i].addEventListener('click', e=>{func(); popUp.style.display='none'}, {once:true});

popUp.append(discard, save);

document.body.append(paarent, popUp);